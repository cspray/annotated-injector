<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactory;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Reflection\Type;
use DI\Container;
use function Cspray\AnnotatedContainer\Reflection\types;

class PhpDiContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory(Emitter $emitter = new Emitter()) : ContainerFactory {
        return new PhpDiContainerFactory(emitter: $emitter);
    }

    protected function getBackingContainerInstanceOf() : Type {
        return types()->class(Container::class);
    }
}
