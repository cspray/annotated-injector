<?php

namespace Cspray\AnnotatedContainer;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class CacheAwareContainerDefinitionCompilerTest extends TestCase {

    private CacheAwareContainerDefinitionCompiler $cacheAwareContainerDefinitionCompiler;
    private PhpParserContainerDefinitionCompiler $phpParserContainerDefinitionCompiler;
    private ContainerDefinitionSerializer $containerDefinitionSerializer;
    private vfsStreamDirectory $root;

    protected function setUp(): void {
        $this->cacheAwareContainerDefinitionCompiler = new CacheAwareContainerDefinitionCompiler(
            $this->phpParserContainerDefinitionCompiler = new PhpParserContainerDefinitionCompiler(),
            $this->containerDefinitionSerializer = new JsonContainerDefinitionSerializer(),
            'vfs://root'
        );
        $this->root = vfsStream::setup();
    }

    public function testFileDoesNotExistWritesFile() {
        $dir = __DIR__ . '/DummyApps/SimpleServices';
        $containerDefinition = $this->cacheAwareContainerDefinitionCompiler->compileDirectory('test', [$dir]);

        $this->assertNotNull($this->root->getChild('root/' . md5('test' . $dir)));

        $expected = $this->containerDefinitionSerializer->serialize($containerDefinition);
        $actual = $this->root->getChild('root/' . md5('test' . $dir))->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testFileDoesExistDoesNotCallCompiler() {
        $dir = __DIR__ . '/DummyApps/EnvironmentResolvedServices';
        $containerDefinition = $this->phpParserContainerDefinitionCompiler->compileDirectory('test', $dir);
        $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);

        vfsStream::newFile(md5('test' . $dir))->at($this->root)->setContent($serialized);

        $mock = $this->getMockBuilder(ContainerDefinitionCompiler::class)->getMock();
        $mock->expects($this->never())->method('compileDirectory');
        $subject = new CacheAwareContainerDefinitionCompiler(
            $mock,
            $this->containerDefinitionSerializer,
            'vfs://root'
        );

        $containerDefinition = $subject->compileDirectory('test', $dir);
        $actual = $this->containerDefinitionSerializer->serialize($containerDefinition);

        $this->assertJsonStringEqualsJsonString($serialized, $actual);
    }

    public function testFailingToWriteCacheFileThrowsException() {
        $dir = __DIR__ . '/DummyApps/EnvironmentResolvedServices';
        $subject = new CacheAwareContainerDefinitionCompiler(
            $this->phpParserContainerDefinitionCompiler = new PhpParserContainerDefinitionCompiler(),
            $this->containerDefinitionSerializer = new JsonContainerDefinitionSerializer(),
            'vfs://cache'
        );


        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The cache directory, vfs://cache, could not be written to. Please ensure it exists and is writeable.');

        $subject->compileDirectory('test', $dir);
    }


}