<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectConstructorServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class ProfilesStringInjectService {

    public function __construct(
        #[Inject('from-dev', profiles: ['dev'])]
        #[Inject('from-test', profiles: ['test'])]
        #[Inject('from-prod', profiles: ['prod'])]
        public readonly string $val
    ) {
    }
}
