<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactory;

use Auryn\Injector;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class AurynContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory(Emitter $emitter = new Emitter()) : ContainerFactory {
        return new AurynContainerFactory(emitter: $emitter);
    }

    protected function getBackingContainerInstanceOf() : ObjectType {
        return objectType(Injector::class);
    }
}
