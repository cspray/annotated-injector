<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

final class VendorPresenceBasedBootstrappingDirectoryResolver implements BootstrappingDirectoryResolver {

    private readonly BootstrappingDirectoryResolver $resolver;

    public function __construct() {
        $rootDir = dirname(__DIR__);
        if (!file_exists($rootDir . '/vendor/autoload.php')) {
            $rootDir = dirname(__DIR__, 5);
        }

        $this->resolver = new RootDirectoryBootstrappingDirectoryResolver($rootDir);
    }

    public function getConfigurationPath(string $subPath) : string {
        return $this->resolver->getConfigurationPath($subPath);
    }

    public function getPathFromRoot(string $subPath) : string {
        return $this->resolver->getPathFromRoot($subPath);
    }

    public function getCachePath(string $subPath) : string {
        return $this->resolver->getCachePath($subPath);
    }

    public function getVendorPath() : string {
        return $this->resolver->getVendorPath();
    }
}
