<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class EnvInjectService {

    public function __construct(
        #[Inject('USER', from: 'env')] public readonly string $user
    ) {
    }
}
