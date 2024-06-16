<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class StringInjectService {

    public function __construct(
        #[Inject('foobar')] public readonly string $val
    ) {
    }
}
