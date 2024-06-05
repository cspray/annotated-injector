<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainerFixture\Fixtures;

final class FixtureBootstrappingDirectoryResolver implements BootstrappingDirectoryResolver {

    public function __construct(private readonly bool $doVendorScanning = false) {
    }

    public function configurationPath(string $subPath) : string {
        return sprintf('vfs://root/%s', $subPath);
    }

    public function pathFromRoot(string $subPath) : string {
        return sprintf('%s/%s', Fixtures::getRootPath(), $subPath);
    }

    public function cachePath(string $subPath) : string {
        return sprintf('vfs://root/%s', $subPath);
    }

    public function getLogPath(string $subPath) : string {
        return sprintf('vfs://root/%s', $subPath);
    }

    public function vendorPath() : string {
        if ($this->doVendorScanning) {
            return sprintf('%s/VendorScanningInitializers/vendor', Fixtures::getRootPath());
        }

        return 'vfs://root/vendor';
    }
}
