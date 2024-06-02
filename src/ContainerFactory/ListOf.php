<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\Typiphy\ObjectType;

/**
 * @template CollectionType
 */
interface ListOf {

    public function type() : ObjectType;

    /**'
     * @param list<object> $servicesOfType
     * @return CollectionType
     */
    public function toCollection(array $servicesOfType) : mixed;

}