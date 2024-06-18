<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\Typiphy\ObjectType;

/**
 * @template ServiceType of object
 * @template CollectionType
 */
interface ListOf {

    public function type() : ObjectType;

    /**'
     * @psalm-param list<ServiceType> $servicesOfType
     * @return CollectionType
     */
    public function toCollection(array $servicesOfType) : mixed;
}
