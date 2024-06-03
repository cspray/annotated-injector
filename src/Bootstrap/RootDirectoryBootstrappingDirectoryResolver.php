<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

final class RootDirectoryBootstrappingDirectoryResolver implements BootstrappingDirectoryResolver {

    public function __construct(
        private readonly string $rootDir
    ) {
    }

    public function getConfigurationPath(string $subPath) : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }

    public function getPathFromRoot(string $subPath) : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }

    public function getCachePath(string $subPath) : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }

    /**
     * @deprecated
     */
    public function getLogPath(string $subPath) : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }

    public function getVendorPath() : string {
        return sprintf('%s/vendor', $this->rootDir);
    }
}
