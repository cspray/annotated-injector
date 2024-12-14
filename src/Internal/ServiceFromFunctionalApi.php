<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;

final class ServiceFromFunctionalApi implements ServiceAttribute {

    public function __construct(
        /**
         * @var list<non-empty-string>
         */
        private readonly array $profiles,
        private readonly bool $isPrimary,
        /**
         * @var ?non-empty-string
         */
        private readonly ?string $name,
    ) {
    }

    public function profiles() : array {
        return $this->profiles;
    }

    public function isPrimary() : bool {
        return $this->isPrimary;
    }

    public function name() : ?string {
        return $this->name;
    }
}
