<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectPrepareServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class ServiceScalarUnionPrepareInjector {

    private FooInterface|float $val;

    public function getValue() : FooInterface|float {
        return $this->val;
    }

    #[ServicePrepare]
    public function setValue(#[Inject(3.14)] FooInterface|float $val) {
        $this->val = $val;
    }
}
