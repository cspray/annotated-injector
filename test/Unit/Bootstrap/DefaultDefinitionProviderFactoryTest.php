<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\DefaultDefinitionProviderFactory;
use Cspray\AnnotatedContainer\Exception\InvalidDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use PHPUnit\Framework\TestCase;

final class DefaultDefinitionProviderFactoryTest extends TestCase {

    public function testDefaultDefinitionProviderFactoryCreatesClassWithNoArguments() : void {
        $subject = new DefaultDefinitionProviderFactory();

        self::assertInstanceOf(
            StubDefinitionProvider::class,
            $subject->createProvider(StubDefinitionProvider::class)
        );
    }

    public function testDefaultDefinitionProviderFactoryNotClassThrowsException() : void {
        $subject = new DefaultDefinitionProviderFactory();

        self::expectException(InvalidDefinitionProvider::class);
        self::expectExceptionMessage(
            'Attempted to create a definition provider, "not a class", that is not a class.'
        );

        $subject->createProvider('not a class');
    }

    public function testDefaultDefinitionProviderFactoryNotParameterStoreClassThrowsException() : void {
        $subject = new DefaultDefinitionProviderFactory();

        self::expectException(InvalidDefinitionProvider::class);
        self::expectExceptionMessage(
            'Attempted to create a definition provider, "' . self::class . '", that is not a ' . DefinitionProvider::class
        );

        $subject->createProvider(self::class);
    }
}
