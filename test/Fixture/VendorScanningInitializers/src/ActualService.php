<?php

namespace Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\Vendor\Package\SomeService;
use Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\Vendor\Package\ThirdPartyDependency;

#[Service]
class ActualService implements SomeService {

    private string $something = '';

    public function __construct(
        public readonly ThirdPartyDependency $thirdPartyDependency
    ) {
    }

    public function setSomething(string $something) : void {
        $this->something = $something;
    }

    public function getSomething() : string {
        return $this->something;
    }
}
