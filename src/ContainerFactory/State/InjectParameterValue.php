<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\State;

/**
 * @internal
 */
final class InjectParameterValue {

    /**
     * @param non-empty-string $name
     * @param mixed $value
     */
    public function __construct(
        public string $name,
        public mixed $value,
    ) {
    }
}
