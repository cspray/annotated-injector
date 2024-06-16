<?php

namespace Cspray\AnnotatedContainer\Fixture\CustomServiceAttribute;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Repository implements ServiceAttribute {

    public function profiles() : array {
        return ['test'];
    }

    public function isPrimary() : bool {
        return false;
    }

    public function name() : ?string {
        return null;
    }
}
