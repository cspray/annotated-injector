<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\MultiplePrepareServices;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class FooImplementation {

    private string $foo;
    private string $bar;

    #[ServicePrepare]
    public function setFoo() : void {
        $this->foo = 'foo';
    }

    #[ServicePrepare]
    public function setBar() : void {
        $this->bar = 'bar';
    }

    public function getProperty() : string {
        return $this->foo .  $this->bar;
    }
}
