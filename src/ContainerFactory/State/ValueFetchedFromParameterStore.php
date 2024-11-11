<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\State;

use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;

/**
 * @internal
 */
final class ValueFetchedFromParameterStore {

    public function __construct(
        private readonly ParameterStore $parameterStore,
        private readonly Type|TypeUnion|TypeIntersect $type,
        private readonly string $value,
    ) {
    }

    public function get() : mixed {
        return $this->parameterStore->fetch($this->type, $this->value);
    }
}
