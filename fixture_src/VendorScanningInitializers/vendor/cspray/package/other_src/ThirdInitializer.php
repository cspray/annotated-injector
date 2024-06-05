<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;

class ThirdInitializer extends ThirdPartyInitializer {

    public function relativeScanDirectories() : array {
        return [];
    }

    public function definitionProviderClass() : ?string {
        return null;
    }

    public function packageName() : string {
        return 'cspray/package';
    }
}