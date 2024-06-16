<?php

namespace Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\Vendor\Package;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface SomeService {

    public function setSomething(string $something) : void;

    public function getSomething() : string;
}
