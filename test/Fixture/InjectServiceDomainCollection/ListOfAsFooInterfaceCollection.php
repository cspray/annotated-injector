<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectServiceDomainCollection;

use Cspray\AnnotatedContainer\ContainerFactory\ListOf;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

/**
 * @implements ListOf<FooInterfaceCollection>
 */
final class ListOfAsFooInterfaceCollection implements ListOf {

    public function __construct(
        private readonly string $type
    ) {
    }

    public function type() : Type {
        return types()->class($this->type);
    }

    public function toCollection(array $servicesOfType) : FooInterfaceCollection {
        return new FooInterfaceCollection(...$servicesOfType);
    }
}
