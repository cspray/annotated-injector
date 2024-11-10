<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Reflection\Type;

/**
 * @template ServiceType of object
 * @template CollectionType
 */
interface ListOf {

    public function type() : Type;

    /**'
     * @psalm-param list<ServiceType> $servicesOfType
     * @return CollectionType
     */
    public function toCollection(array $servicesOfType) : mixed;
}
