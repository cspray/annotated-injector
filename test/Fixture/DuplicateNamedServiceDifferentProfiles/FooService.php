<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\DuplicateNamedServiceDifferentProfiles;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['dev'], name: 'service')]
class FooService {

}
