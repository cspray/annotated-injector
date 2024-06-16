<?php

namespace Cspray\AnnotatedContainer\Fixture\InjectListOfScalars;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
final class ListInjector {

    public function __construct(
        #[Inject([1, 2, 3])]
        public readonly array $ints,
        #[Inject([1.1, 2.2, 3.3])]
        public readonly array $floats,
        #[Inject([true, false, true])]
        public readonly array $bools,
        #[Inject([null, null, null])]
        public readonly array $nulls
    ) {
    }
}
