<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;

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