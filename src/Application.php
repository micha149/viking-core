<?php
/*
 * This file is part of Viking CMS
 *
 * (c) 2014 Michael van Engelshoven
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Viking;

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Repository\ComposerRepository;
use Composer\Repository\InstalledFilesystemRepository;
use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRouteEnhancersPass;
use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRoutersPass;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Viking\Config\CoreConfiguration;
use Viking\Plugin\PluginLoader;
use Viking\Templating\RegisterEnginePass;
use Webmozart\PathUtil\Path;

/**
 * Class Application
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking
 */
class Application implements HttpKernelInterface, TerminableInterface {

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->booted = false;

        $this->config = $this->processConfig($config);
        $this->controllerRoot = $this->appRoot . '/controllers';
    }

    protected function boot() {

        if ($this->booted) {
            return;
        }

        $file = $this->config['root_dir'] .'/cache/container.php';
        $isDebug = $this->config['debug'];

        if (!$isDebug && file_exists($file)) {
            require_once $file;
            $this->container = new \VikingContainer();
        } else {
            $this->container = $this->buildContainer();
            $this->container->compile();
            if (!$isDebug) {
                $dumper = new PhpDumper($this->container);
                file_put_contents(
                    $file,
                    $dumper->dump(array('class' => 'VikingContainer'))
                );
            }
        }

        $this->booted = true;
    }

    protected function buildContainer() {
        $container = new ContainerBuilder(new ParameterBag($this->getAppParameters()));
        $container->set('app', $this);
        $container->addScope(new Scope('request'));

        $container->addCompilerPass(new RegisterListenersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new RegisterRoutersPass('routing.chain_router', 'router'));
        $container->addCompilerPass(new RegisterRouteEnhancersPass('routing.dynamic_router', 'route_enhancer'));
        $container->addCompilerPass(new RegisterEnginePass());

        $configLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/services"));
        $configLoader->load('services.yml');

        $pluginLoader = new PluginLoader($container, new FileLocator($this->config['root_dir']));
        $pluginLoader->load();

        return $container;
    }

    protected function getAppParameters() {

        $config = array(
            'app.secret' => $this->config["secret"],
            'app.root_dir' => $this->config["root_dir"],
            'app.debug' => $this->config["debug"],
            'app.controller_dir' => $this->config["root"] . '/' .$this->config["controller_folder"],
            'app.startpage' => $this->config["startpage"]
        );

        return $config;
    }

    /**
     * Processes the given app configuration
     * @param array $config
     * @return array
     */
    protected function processConfig(array $config) {
        $processor = new Processor();
        $configuration = new CoreConfiguration();
        return $processor->processConfiguration($configuration, $config);
    }

    /**
     * Handles the request and delivers the response.
     *
     * @param Request|null $request Request to process
     */
    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createFromGlobals();
        }

        $response = $this->handle($request);
        $response->send();
        $this->terminate($request, $response);
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param int $type The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        if (!$this->booted) {
            $this->boot();
        }

        return $this->container->get('http_kernel')->handle($request, $type, $catch);
    }

    /**
     * Terminates a request/response cycle.
     *
     * Should be called after sending the response and before shutting down the kernel.
     *
     * @param Request $request A Request instance
     * @param Response $response A Response instance
     *
     * @api
     */
    public function terminate(Request $request, Response $response)
    {
        return $this->container->get('http_kernel')->terminate($request, $response);
    }
}