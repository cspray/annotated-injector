<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\State;

use Cspray\AnnotatedContainer\ContainerFactory\ListOf;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;

/**
 * @internal
 */
final class ServiceCollectorReference {

    /**
     * @template ServiceType of object
     * @template CollectionType
     * @param ListOf<ServiceType, CollectionType> $listOf
     * @param Type $valueType
     * @param Type|TypeUnion|TypeIntersect $collectionType
     */
    public function __construct(
        public readonly ListOf     $listOf,
        public readonly Type $valueType,
        public readonly Type|TypeUnion|TypeIntersect $collectionType
    ) {
    }
}
