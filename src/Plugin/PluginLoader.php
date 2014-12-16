<?php
/*
 * This file is part of Viking CMS
 *
 * (c) 2014 Michael van Engelshoven
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Viking\Plugin;

use Composer\Json\JsonFile;
use Composer\Package\AliasPackage;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledFilesystemRepository;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;

/**
 * Class PluginLoader
 *
 * @author Michael van Engelshoven <michael@van-engelshoven.de>
 * @package Viking\Plugin
 */
class PluginLoader {

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var FileLocatorInterface
     */
    private $locator;

    public function __construct(ContainerBuilder $container, FileLocatorInterface $locator) {
        $this->container = $container;
        $this->locator = $locator;
    }

    public function load() {
        $repo = $this->getComposerRepository();
        $aliases = array();

        /** @var PackageInterface $package */
        foreach ($repo->getPackages() as $package) {
            if ($package instanceof AliasPackage) {
                continue;
            }
            if ('viking-plugin' === $package->getType()) {
                $plugin = $this->createPluginInstance($package);

                $aliases[] = $plugin->getAlias();
                $this->container->registerExtension($plugin);
            }
        }

        $this->container->addCompilerPass(new MergeExtensionConfigurationPass($aliases));
    }

    /**
     * @return JsonFile
     */
    protected function getConfig()
    {
        $path = $this->locator->locate('vendor/composer/installed.json');
        $file = new JsonFile($path);
        return $file;
    }

    /**
     * @return InstalledFilesystemRepository
     */
    protected function getComposerRepository()
    {
        $config = $this->getConfig();
        $repo = new InstalledFilesystemRepository($config);
        return $repo;
    }

    /**
     * @param PackageInterface $package
     * @return PluginInterface
     */
    protected function createPluginInstance(PackageInterface $package)
    {
        $nameParts = explode('/', $package->getName());
        $canonicalName = ContainerBuilder::camelize(str_replace('-', '_', $nameParts[1]));
        $class = 'Viking\\' . $canonicalName;
        $path = $this->locator->locate('vendor/' . $package->getName() . '/' . $canonicalName . '.php');

        require_once $path;
        return new $class();
    }
} 