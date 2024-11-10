<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;

/**
 * @internal
 */
final class ServiceDelegateFromFunctionalApi implements ServiceDelegateAttribute {

    public function __construct(
        /**
         * @var list<non-empty-string>
         */
        private readonly array $profiles = [],
    ) {
    }

    public function service() : ?string {
        return null;
    }

    /**
     * @return list<non-empty-string>
     */
    public function profiles() : array {
        return $this->profiles;
    }
}
