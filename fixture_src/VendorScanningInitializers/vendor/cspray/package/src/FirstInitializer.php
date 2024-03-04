<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;

// Combined with the #[Service] attribute on SomeService ensures this package
// source directory is scanned
final class FirstInitializer extends ThirdPartyInitializer {

    public function getRelativeScanDirectories() : array {
        return [
            'src',
            'other_src'
        ];
    }

    public function getDefinitionProviderClass() : ?string {
        return null;
    }

    public function getPackageName() : string {
        return 'cspray/package';
    }
}