<?php

namespace Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\Vendor\Package;

use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;
use Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\DependencyDefinitionProvider;

// Ensures that the ThirdPartyDependency is provided, through the DependencyDefinitionProvider

final class SecondInitializer extends ThirdPartyInitializer {

    public function relativeScanDirectories() : array {
        return [];
    }

    public function definitionProviderClass() : string {
        return DependencyDefinitionProvider::class;
    }

    public function packageName() : string {
        return 'cspray/package';
    }
}
