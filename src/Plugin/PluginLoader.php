<?php
/**
 * Created by PhpStorm.
 * User: micha149
 * Date: 25.11.14
 * Time: 08:02
 */

namespace Viking\Plugin;


use Composer\Json\JsonFile;
use Composer\Package\AliasPackage;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledFilesystemRepository;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PluginLoader {

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var FileLocatorInterface
     */
    private $locator;

    public function __construct(ContainerInterface $container, FileLocatorInterface $locator) {
        $this->container = $container;
        $this->locator = $locator;
    }

    public function load() {
        $repo = $this->getComposerRepository();

        /** @var PackageInterface $package */
        foreach ($repo->getPackages() as $package) {
            if ($package instanceof AliasPackage) {
                continue;
            }
            if ('viking-plugin' === $package->getType()) {
                $this->registerPlugin($package);
            }
        }
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

    protected function registerPlugin(PackageInterface $package)
    {
        $nameParts = explode('/', $package->getName());
        $configLoader = new YamlFileLoader($this->container, $this->locator);
        $configLoader->load('vendor/' . $package->getName() . '/'. $nameParts[1] . '.yml');
    }
} 