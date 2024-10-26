<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactory;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\IlluminateContainerFactory;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Reflection\Type;
use Illuminate\Contracts\Container\Container;
use function Cspray\AnnotatedContainer\Reflection\types;

class IlluminateContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory(Emitter $emitter = new Emitter()) : ContainerFactory {
        return new IlluminateContainerFactory(emitter: $emitter);
    }

    protected function getBackingContainerInstanceOf() : Type {
        return types()->class(Container::class);
    }

    protected function supportsInjectingMultipleNamedServices() : bool {
        return false;
    }
}
