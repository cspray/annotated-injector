<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class NullableStringInjectService {

    public function __construct(
        #[Inject(null)] public readonly ?string $maybe
    ) {
    }
}
