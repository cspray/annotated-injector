<?php

namespace Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\Vendor\Package;

use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;

// Combined with the #[Service] attribute on SomeService ensures this package
// source directory is scanned
final class FirstInitializer extends ThirdPartyInitializer {

    public function relativeScanDirectories() : array {
        return [
            'src',
            'other_src'
        ];
    }

    public function definitionProviderClass() : ?string {
        return null;
    }

    public function packageName() : string {
        return 'cspray/package';
    }
}
