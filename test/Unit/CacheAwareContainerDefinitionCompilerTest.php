<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\CacheAwareContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\Exception\InvalidCache;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Unit\Helper\TestLogger;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class CacheAwareContainerDefinitionCompilerTest extends TestCase {

    private CacheAwareContainerDefinitionAnalyzer $cacheAwareContainerDefinitionCompiler;
    private AnnotatedTargetContainerDefinitionAnalyzer $phpParserContainerDefinitionCompiler;
    private ContainerDefinitionSerializer $containerDefinitionSerializer;
    private vfsStreamDirectory $root;

    protected function setUp(): void {
        $this->cacheAwareContainerDefinitionCompiler = new CacheAwareContainerDefinitionAnalyzer(
            $this->phpParserContainerDefinitionCompiler = new AnnotatedTargetContainerDefinitionAnalyzer(
                new PhpParserAnnotatedTargetParser(),
                new AnnotatedTargetDefinitionConverter()
            ),
            $this->containerDefinitionSerializer = new ContainerDefinitionSerializer(),
            'vfs://root'
        );
        $this->root = vfsStream::setup();
    }

    public function testFileDoesNotExistWritesFile() {
        $dir = Fixtures::singleConcreteService()->getPath();
        $containerDefinition = $this->cacheAwareContainerDefinitionCompiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir)->build()
        );

        $this->assertNotNull($this->root->getChild('root/' . md5($dir)));

        $expected = $this->containerDefinitionSerializer->serialize($containerDefinition);
        $actual = $this->root->getChild('root/' . md5($dir))->getContent();

        $this->assertSame($expected, $actual);
    }

    public function testFileDoesExistDoesNotCallCompiler() {
        $dir = Fixtures::implicitAliasedServices()->getPath();
        $containerDefinition = $this->phpParserContainerDefinitionCompiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir)->build()
        );
        $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);

        vfsStream::newFile(md5($dir))->at($this->root)->setContent($serialized);

        $mock = $this->getMockBuilder(ContainerDefinitionAnalyzer::class)->getMock();
        $mock->expects($this->never())->method('analyze');
        $subject = new CacheAwareContainerDefinitionAnalyzer(
            $mock,
            $this->containerDefinitionSerializer,
            'vfs://root'
        );

        $containerDefinition = $subject->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir)->build()
        );
        $actual = $this->containerDefinitionSerializer->serialize($containerDefinition);

        $this->assertSame($serialized, $actual);
    }

    public function testMultipleDirectoriesCachedRegardlessOfOrder() {
        $implicitDir = Fixtures::implicitAliasedServices()->getPath();
        $concreteDir = Fixtures::singleConcreteService()->getPath();
        $containerDefinition = $this->phpParserContainerDefinitionCompiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($concreteDir, $implicitDir)->build()
        );
        $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);

        vfsStream::newFile(md5($implicitDir . $concreteDir))->at($this->root)->setContent($serialized);

        $mock = $this->getMockBuilder(ContainerDefinitionAnalyzer::class)->getMock();
        $mock->expects($this->never())->method('analyze');
        $subject = new CacheAwareContainerDefinitionAnalyzer(
            $mock,
            $this->containerDefinitionSerializer,
            'vfs://root'
        );

        $containerDefinition = $subject->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($concreteDir, $implicitDir)->build()
        );
        $actual = $this->containerDefinitionSerializer->serialize($containerDefinition);

        $this->assertSame($serialized, $actual);
    }

    public function testFailingToWriteCacheFileThrowsException() {
        $dir = Fixtures::implicitAliasedServices()->getPath();
        $subject = new CacheAwareContainerDefinitionAnalyzer(
            $this->phpParserContainerDefinitionCompiler = new AnnotatedTargetContainerDefinitionAnalyzer(
                new PhpParserAnnotatedTargetParser(),
                new AnnotatedTargetDefinitionConverter()
            ),
            $this->containerDefinitionSerializer,
            'vfs://cache'
        );


        $this->expectException(InvalidCache::class);
        $this->expectExceptionMessage('The cache directory, vfs://cache, could not be written to. Please ensure it exists and is writeable.');

        $subject->analyze(ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir)->build());
    }

    public function testFileDoesExistLogsOutput() {
        $dir = Fixtures::implicitAliasedServices()->getPath();
        $containerDefinition = $this->phpParserContainerDefinitionCompiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir)
                ->build()
        );
        $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);

        vfsStream::newFile(md5($dir))->at($this->root)->setContent($serialized);

        $mock = $this->getMockBuilder(ContainerDefinitionAnalyzer::class)->getMock();
        $mock->expects($this->never())->method('analyze');
        $subject = new CacheAwareContainerDefinitionAnalyzer(
            $mock,
            $this->containerDefinitionSerializer,
            'vfs://root'
        );

        $logger = new TestLogger();
        $subject->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir)
                ->withLogger($logger)
                ->build()
        );

        $expected = [
            'message' => 'Skipping Annotated Container compiling. Using cached definition from vfs://root/' . md5($dir) . '.',
            'context' => []
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testCacheFileVersionMismatchRecompiles() : void {
        $attrVal = base64_encode(serialize(new Service()));
        $dir = Fixtures::singleConcreteService()->getPath();
        $oldXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="0.1">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute>{$attrVal}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        vfsStream::newFile(md5($dir))->at($this->root)->setContent($oldXml);

        $this->cacheAwareContainerDefinitionCompiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir)->build()
        );

        $version = AnnotatedContainerVersion::getVersion();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute>{$attrVal}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        self::assertStringEqualsFile('vfs://root/' . md5($dir), $expected);
    }
}
