<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectServiceCollectionDecorator;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\ContainerFactory\ListOfAsArray;

#[Service(primary: true)]
class CompositeFooImplementation implements FooInterface {

    public function __construct(
        #[Inject(new ListOfAsArray(FooInterface::class))]
        public readonly array $foos
    ) {
    }
}
