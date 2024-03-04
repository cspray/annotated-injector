<?php

namespace Cspray\AnnotatedContainerFixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;

// Ensures that the ThirdPartyDependency is provided, through the DependencyDefinitionProvider

final class SecondInitializer extends ThirdPartyInitializer {

    public function getRelativeScanDirectories() : array {
        return [];
    }

    public function getDefinitionProviderClass() : string {
        return DependencyDefinitionProvider::class;
    }

    public function getPackageName() : string {
        return 'cspray/package';
    }
}