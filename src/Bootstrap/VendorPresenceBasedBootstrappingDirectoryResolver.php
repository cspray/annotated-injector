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

    public function configurationPath(string $subPath) : string {
        return $this->resolver->configurationPath($subPath);
    }

    public function pathFromRoot(string $subPath) : string {
        return $this->resolver->pathFromRoot($subPath);
    }

    public function cachePath(string $subPath) : string {
        return $this->resolver->cachePath($subPath);
    }

    public function vendorPath() : string {
        return $this->resolver->vendorPath();
    }
}
