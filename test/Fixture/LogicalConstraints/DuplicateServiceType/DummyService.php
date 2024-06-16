<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceType;

use Attribute;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DummyService implements ServiceAttribute {

    public function profiles() : array {
        return [];
    }

    public function isPrimary() : bool {
        return false;
    }

    public function name() : ?string {
        return null;
    }
}
