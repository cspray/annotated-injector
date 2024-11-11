<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\MultipleInjectDefinition;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
final class InjectService {

    public function __construct(
        #[Inject('foo')]
        #[Inject('bar')]
        public readonly string $value
    ) {
    }
}
