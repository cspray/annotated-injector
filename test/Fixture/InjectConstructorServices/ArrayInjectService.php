<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class ArrayInjectService {

    public function __construct(
        #[Inject(['dependency', 'injection', 'rocks'])]
        public readonly array $values
    ) {
    }
}
