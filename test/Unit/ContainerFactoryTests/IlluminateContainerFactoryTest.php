<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactoryTests;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\IlluminateContainerFactory;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Unit\ContainerFactoryTestCase;
use Cspray\Typiphy\ObjectType;
use Illuminate\Contracts\Container\Container;
use function Cspray\Typiphy\objectType;

class IlluminateContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory(Emitter $emitter = new Emitter()) : ContainerFactory {
        return new IlluminateContainerFactory(emitter: $emitter);
    }

    protected function getBackingContainerInstanceOf() : ObjectType {
        return objectType(Container::class);
    }

    protected function supportsInjectingMultipleNamedServices() : bool {
        return false;
    }
}
