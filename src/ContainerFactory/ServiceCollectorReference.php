<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;

/**
 * @internal
 */
final class ServiceCollectorReference {

    public function __construct(
        public readonly ListOf     $listOf,
        public readonly ObjectType $valueType,
        public readonly Type|TypeUnion|TypeIntersect $collectionType
    ) {}

}
