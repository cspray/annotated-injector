<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Definition\Cache;

use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\Definition\Cache\FileBackedContainerDefinitionCache;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Definition\Serializer\SerializedContainerDefinition;
use Cspray\AnnotatedContainer\Exception\CacheDirectoryNotFound;
use Cspray\AnnotatedContainer\Exception\CacheDirectoryNotWritable;
use Cspray\AnnotatedContainer\Exception\MismatchedContainerDefinitionSerializerVersions;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class FileBackedContainerDefinitionCacheTest extends TestCase {

    private FileBackedContainerDefinitionCache $subject;
    private MockObject&ContainerDefinitionSerializer $serializer;
    private vfsStreamDirectory $root;
    private CacheKey $cacheKey;

    protected function setUp() : void {
        $this->serializer = $this->getMockBuilder(ContainerDefinitionSerializer::class)->getMock();
        $this->root = vfsStream::setup('root', 0777);
        $this->cacheKey = CacheKey::fromContainerDefinitionAnalysisOptions(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories('foo', 'bar', 'baz')->build()
        );
    }

    public function testDirectoryNotWritableThrowsException() : void {
        $this->root->chmod(0444);

        $this->expectException(CacheDirectoryNotWritable::class);
        $this->expectExceptionMessage(
            'The cache directory configured, "vfs://root", is not writable.'
        );

        new FileBackedContainerDefinitionCache($this->serializer, $this->root->url());
    }

    public function testDirectoryNotPresentThrowsException() : void {
        $this->expectException(CacheDirectoryNotFound::class);
        $this->expectExceptionMessage(
            'The cache directory configured, "vfs://not-found", is not present.'
        );

        new FileBackedContainerDefinitionCache($this->serializer, 'vfs://not-found');
    }

    public function testGetForKeyWithNoCacheFilePresentReturnsNull() : void {
        $subject = new FileBackedContainerDefinitionCache($this->serializer, $this->root->url());

        self::assertNull($subject->get($this->cacheKey));
    }

    public function testGetForKeyWithCacheFilePresentPassesToSerializerAndReturnsContainerDefinition() : void {
        $subject = new FileBackedContainerDefinitionCache($this->serializer, $this->root->url());

        vfsStream::newFile($this->cacheKey->asString())->at($this->root)->setContent('my-serialized-container');
        $containerDefinition = $this->getMockBuilder(ContainerDefinition::class)->getMock();
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($this->callback(
                fn(SerializedContainerDefinition $serializedContainerDefinition) =>
                    $serializedContainerDefinition->asString() === 'my-serialized-container'
            ))->willReturn($containerDefinition);

        $actual = $subject->get($this->cacheKey);

        self::assertSame($containerDefinition, $actual);
    }

    public function testSetWithCacheKeyCreatesNewFile() : void {
        $subject = new FileBackedContainerDefinitionCache($this->serializer, $this->root->url());

        $containerDefinition = $this->getMockBuilder(ContainerDefinition::class)->getMock();
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($containerDefinition)
            ->willReturn(SerializedContainerDefinition::fromString('serialized-content'));

        self::assertNull($this->root->getChild($this->cacheKey->asString()));

        $subject->set($this->cacheKey, $containerDefinition);

        $file = $this->root->getChild($this->cacheKey->asString());
        self::assertNotNull($file);
        self::assertSame('serialized-content', $file->getContent());
    }

    public function testRemoveWithCacheKeyFilePresentDeletesFile() : void {
        vfsStream::newFile($this->cacheKey->asString())->at($this->root)->setContent('my-serialized-container-def');

        $subject = new FileBackedContainerDefinitionCache($this->serializer, $this->root->url());
        $subject->remove($this->cacheKey);

        self::assertNull($this->root->getChild($this->cacheKey->asString()));
    }

    public function testRemoveHandlesCacheKeyFileNotPresent() : void {
        $subject = new FileBackedContainerDefinitionCache($this->serializer, $this->root->url());
        $subject->remove($this->cacheKey);

        self::assertNull($this->root->getChild($this->cacheKey->asString()));
    }

    public function testGetWithMismatchedVersionRemovesOffendingCacheAndReturnsNull() : void {
        $subject = new FileBackedContainerDefinitionCache($this->serializer, $this->root->url());

        vfsStream::newFile($this->cacheKey->asString())->at($this->root)->setContent('my-serialized-container');
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($this->callback(
                fn(SerializedContainerDefinition $serializedContainerDefinition) =>
                    $serializedContainerDefinition->asString() === 'my-serialized-container'
            ))->willThrowException(MismatchedContainerDefinitionSerializerVersions::fromVersionIsNotInstalledAnnotatedContainerVersion('1.0'));

        $actual = $subject->get($this->cacheKey);

        self::assertNull($actual);
        self::assertNull($this->root->getChild($this->cacheKey->asString()));
    }
}
