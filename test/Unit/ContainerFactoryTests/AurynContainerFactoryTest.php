<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactoryTests;

use Auryn\Injector;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\AnnotatedContainer\Unit\ContainerFactoryTestCase;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class AurynContainerFactoryTest extends ContainerFactoryTestCase {

    protected function getContainerFactory(ActiveProfiles $activeProfiles) : ContainerFactory {
        return new AurynContainerFactory();
    }

    protected function getBackingContainerInstanceOf() : ObjectType {
        return objectType(Injector::class);
    }
}
