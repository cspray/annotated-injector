<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;

/**
 * @internal
 */
final class ServiceCollectorReference {

    public function __construct(
        public readonly ListOf     $listOf,
        public readonly Type $valueType,
        public readonly Type|TypeUnion|TypeIntersect $collectionType
    ) {
    }
}
