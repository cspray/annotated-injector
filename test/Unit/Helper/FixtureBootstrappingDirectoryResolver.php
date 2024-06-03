<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainerFixture\Fixtures;

final class FixtureBootstrappingDirectoryResolver implements BootstrappingDirectoryResolver {

    public function __construct(private readonly bool $doVendorScanning = false) {
    }

    public function getConfigurationPath(string $subPath) : string {
        return sprintf('vfs://root/%s', $subPath);
    }

    public function getPathFromRoot(string $subPath) : string {
        return sprintf('%s/%s', Fixtures::getRootPath(), $subPath);
    }

    public function getCachePath(string $subPath) : string {
        return sprintf('vfs://root/%s', $subPath);
    }

    public function getLogPath(string $subPath) : string {
        return sprintf('vfs://root/%s', $subPath);
    }

    public function getVendorPath() : string {
        if ($this->doVendorScanning) {
            return sprintf('%s/VendorScanningInitializers/vendor', Fixtures::getRootPath());
        }

        return 'vfs://root/vendor';
    }
}
