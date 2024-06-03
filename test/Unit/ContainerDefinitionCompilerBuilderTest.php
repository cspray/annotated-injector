<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzerBuilder;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;

final class ContainerDefinitionCompilerBuilderTest extends TestCase {

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testInstanceWithoutCache() : void {
        $compiler = ContainerDefinitionAnalyzerBuilder::withoutCache()->build();

        $containerDefinition = $compiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );

        self::assertCount(1, $containerDefinition->getServiceDefinitions());
    }

    public function testInstanceWithCache() : void {
        $compiler = ContainerDefinitionAnalyzerBuilder::withCache('vfs://root')->build();

        $containerDefinition = $compiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(Fixtures::singleConcreteService()->getPath())->build()
        );

        self::assertCount(1, $containerDefinition->getServiceDefinitions());
        self::assertFileExists('vfs://root/' . md5(Fixtures::singleConcreteService()->getPath()));
    }
}
