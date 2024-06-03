<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\DefaultParameterStoreFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Exception\InvalidParameterStore;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore;
use PHPUnit\Framework\TestCase;

final class DefaultParameterStoreFactoryTest extends TestCase {

    public function testDefaultParameterFactoryCreatesClassWithNoArguments() : void {
        $subject = new DefaultParameterStoreFactory();

        self::assertInstanceOf(
            StubParameterStore::class,
            $subject->createParameterStore(StubParameterStore::class)
        );
    }

    public function testDefaultParameterStoreFactoryNotClassThrowsException() : void {
        $subject = new DefaultParameterStoreFactory();

        self::expectException(InvalidParameterStore::class);
        self::expectExceptionMessage(
            'Attempted to create a parameter store, "not a class", that is not a class.'
        );

        $subject->createParameterStore('not a class');
    }

    public function testDefaultParameterStoreFactoryNotParameterStoreClassThrowsException() : void {
        $subject = new DefaultParameterStoreFactory();

        self::expectException(InvalidParameterStore::class);
        self::expectExceptionMessage(
            'Attempted to create a parameter store, "' . $this::class . '", that is not a ' . ParameterStore::class
        );

        $subject->createParameterStore($this::class);
    }
}
