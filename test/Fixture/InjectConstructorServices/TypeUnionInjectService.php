<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class TypeUnionInjectService {

    public function __construct(
        #[Inject(4.20)] public readonly string|int|float $value
    ) {
    }
}
