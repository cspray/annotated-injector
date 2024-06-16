<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectServiceDomainCollection;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
final class CollectionInjector {

    public function __construct(
        #[Inject(new ListOfAsFooInterfaceCollection(FooInterface::class))]
        public readonly FooInterfaceCollection $collection
    ) {
    }
}
