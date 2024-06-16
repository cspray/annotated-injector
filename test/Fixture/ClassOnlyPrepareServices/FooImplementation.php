<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\Fixture\ClassOnlyPrepareServices;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class FooImplementation implements FooInterface {

    private int $barCounter = 0;

    #[ServicePrepare]
    public function setBar() {
        $this->barCounter++;
    }

    public function getBarCounter() : int {
        return $this->barCounter;
    }
}
