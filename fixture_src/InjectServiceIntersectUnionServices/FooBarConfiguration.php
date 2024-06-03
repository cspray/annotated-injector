<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectServiceIntersectUnionServices;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;

#[Configuration]
class FooBarConfiguration {

    #[Inject(FooBarImplementation::class)]
    private readonly FooInterface&BarInterface $fooAndBar;

    #[Inject(BarImplementation::class)]
    private readonly FooInterface|BarInterface $fooOrBar;

}