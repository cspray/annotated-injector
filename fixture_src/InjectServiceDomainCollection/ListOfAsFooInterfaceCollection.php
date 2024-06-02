<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectServiceDomainCollection;

use Cspray\AnnotatedContainer\ContainerFactory\ListOf;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

/**
 * @implements ListOf<FooInterfaceCollection>
 */
final class ListOfAsFooInterfaceCollection implements ListOf {

    public function __construct(
        private readonly string $type
    ) {}

    public function type() : ObjectType {
        return objectType($this->type);
    }

    public function toCollection(array $servicesOfType) : FooInterfaceCollection {
        return new FooInterfaceCollection(...$servicesOfType);
    }
}