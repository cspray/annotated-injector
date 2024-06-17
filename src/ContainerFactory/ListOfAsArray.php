<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

/**
 * @template ItemType of object
 * @implements ListOf<ItemType, list<ItemType>>
 */
final class ListOfAsArray implements ListOf {

    /**
     * @param class-string<ItemType> $type
     */
    public function __construct(
        private readonly string $type
    ) {
    }

    public function type() : ObjectType {
        return objectType($this->type);
    }

    public function toCollection(array $servicesOfType) : array {
        return $servicesOfType;
    }
}
