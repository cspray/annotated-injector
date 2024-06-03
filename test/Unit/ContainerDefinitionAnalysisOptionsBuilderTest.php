<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\StaticAnalysis\CallableDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ContainerDefinitionAnalysisOptionsBuilderTest extends TestCase {

    public function testByDefaultDefinitionProviderIsNull() : void {
        $compilerOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build();

        self::assertNull($compilerOptions->getDefinitionProvider());
    }

    public function testByDefaultLoggerIsNull() : void {
        $compilerOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build();

        self::assertNull($compilerOptions->getLogger());
    }

    public function testWithDefinitionProviderImmutable() : void {
        $a = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath());
        $b = $a->withDefinitionProvider(new CallableDefinitionProvider(function() {
        }));

        self::assertNotSame($a, $b);
    }

    public function testWithDefinitionProviderReturnsCorrectInstance() : void {
        $compilerOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())
            ->withDefinitionProvider($expected = new CallableDefinitionProvider(function() {
            }))
            ->build();

        self::assertSame($expected, $compilerOptions->getDefinitionProvider());
    }

    public function testWithLoggerImmutable() : void {
        $a = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath());
        $b = $a->withLogger($this->getMockBuilder(LoggerInterface::class)->getMock());

        self::assertNotSame($a, $b);
    }

    public function testWithLoggerReturnsLogger() : void {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $compilerOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())
            ->withLogger($logger)
            ->build();

        self::assertSame($logger, $compilerOptions->getLogger());
    }
}
