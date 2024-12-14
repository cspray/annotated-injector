<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\State;

use Cspray\AnnotatedContainer\Reflection\Type;

/**
 * @internal
 */
final class ContainerReference {

    /**
     * @param non-empty-string $name
     * @param Type $type
     */
    public function __construct(
        public readonly string $name,
        public readonly Type $type
    ) {
    }
}
