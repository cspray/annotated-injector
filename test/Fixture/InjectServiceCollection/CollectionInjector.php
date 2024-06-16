<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectServiceCollection;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\ContainerFactory\ListOf;
use Cspray\AnnotatedContainer\ContainerFactory\ListOfAsArray;

#[Service]
final class CollectionInjector {

    public function __construct(
        #[Inject(new ListOfAsArray(FooInterface::class))]
        public readonly array $services
    ) {
    }
}
