<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectPrepareServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class PrepareInjector {

    private string $val;
    private FooInterface $service;

    #[ServicePrepare]
    public function setVals(
        #[Inject('foo')] string $val,
        #[Inject(BarImplementation::class)] FooInterface $service
    ) : void {
        $this->val = $val;
        $this->service = $service;
    }

    public function getVal() : string {
        return $this->val;
    }

    public function getService() : FooInterface {
        return $this->service;
    }
}
