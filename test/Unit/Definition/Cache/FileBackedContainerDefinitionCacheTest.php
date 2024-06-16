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
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class FileBackedContainerDefinitionCacheTest extends TestCase {

    private MockObject&ContainerDefinitionSerializer $serializer;
    private MockObject&Filesystem $filesystem;
    private CacheKey $cacheKey;

    protected function setUp() : void {
        $this->serializer = $this->createMock(ContainerDefinitionSerializer::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->cacheKey = CacheKey::fromContainerDefinitionAnalysisOptions(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories('foo', 'bar', 'baz')->build()
        );
    }

    public function testDirectoryNotPresentThrowsException() : void {
        $this->filesystem->expects($this->once())
            ->method('isDirectory')
            ->with('/cache/dir')
            ->willReturn(false);

        $this->expectException(CacheDirectoryNotFound::class);
        $this->expectExceptionMessage(
            'The cache directory configured, "/cache/dir", is not present.'
        );

        new FileBackedContainerDefinitionCache(
            $this->serializer,
            $this->filesystem,
            '/cache/dir'
        );
    }

    public function testDirectoryNotWritableThrowsException() : void {
        $this->filesystem->expects($this->once())
            ->method('isDirectory')
            ->with('/cache/dir')
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('isWritable')
            ->with('/cache/dir')
            ->willReturn(false);

        $this->expectException(CacheDirectoryNotWritable::class);
        $this->expectExceptionMessage(
            'The cache directory configured, "/cache/dir", is not writable.'
        );

        new FileBackedContainerDefinitionCache(
            $this->serializer,
            $this->filesystem,
            '/cache/dir'
        );
    }

    public function testGetForKeyWithNoCacheFilePresentReturnsNull() : void {
        $this->filesystem->expects($this->once())
            ->method('isDirectory')
            ->with('/cache/dir')
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('isWritable')
            ->with('/cache/dir')
            ->willReturn(true);

        $subject = new FileBackedContainerDefinitionCache(
            $this->serializer,
            $this->filesystem,
            '/cache/dir'
        );

        $this->filesystem->expects($this->once())
            ->method('isFile')
            ->with('/cache/dir/' . $this->cacheKey->asString())
            ->willReturn(false);

        self::assertNull($subject->get($this->cacheKey));
    }

    public function testGetForKeyWithCacheFilePresentPassesToSerializerAndReturnsContainerDefinition() : void {
        $this->filesystem->expects($this->once())
            ->method('isDirectory')
            ->with('/cache/dir')
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('isWritable')
            ->with('/cache/dir')
            ->willReturn(true);

        $subject = new FileBackedContainerDefinitionCache(
            $this->serializer,
            $this->filesystem,
            '/cache/dir'
        );

        $this->filesystem->expects($this->once())
            ->method('isFile')
            ->with('/cache/dir/' . $this->cacheKey->asString())
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/cache/dir/' . $this->cacheKey->asString())
            ->willReturn('my-serialized-container');

        $containerDefinition = $this->getMockBuilder(ContainerDefinition::class)->getMock();
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($this->callback(
                static fn(SerializedContainerDefinition $serializedContainerDefinition) =>
                    $serializedContainerDefinition->asString() === 'my-serialized-container'
            ))->willReturn($containerDefinition);

        $actual = $subject->get($this->cacheKey);

        self::assertSame($containerDefinition, $actual);
    }

    public function testSetWithCacheKeyCreatesNewFile() : void {
        $this->filesystem->expects($this->once())
            ->method('isDirectory')
            ->with('/cache/dir')
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('isWritable')
            ->with('/cache/dir')
            ->willReturn(true);

        $subject = new FileBackedContainerDefinitionCache(
            $this->serializer,
            $this->filesystem,
            '/cache/dir'
        );

        $containerDefinition = $this->getMockBuilder(ContainerDefinition::class)->getMock();
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($containerDefinition)
            ->willReturn(SerializedContainerDefinition::fromString('serialized-content'));

        $this->filesystem->expects($this->once())
            ->method('write')
            ->with('/cache/dir/' . $this->cacheKey->asString(), 'serialized-content');

        $subject->set($this->cacheKey, $containerDefinition);
    }

    public function testRemoveCallsFilesystemRemoveWithCorrectPath() : void {
        $this->filesystem->expects($this->once())
            ->method('isDirectory')
            ->with('/path/to/cache')
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('isWritable')
            ->with('/path/to/cache')
            ->willReturn(true);

        $subject = new FileBackedContainerDefinitionCache(
            $this->serializer,
            $this->filesystem,
            '/path/to/cache'
        );

        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with('/path/to/cache/' . $this->cacheKey->asString());

        $subject->remove($this->cacheKey);
    }

    public function testGetWithMismatchedVersionRemovesOffendingCacheAndReturnsNull() : void {
        $this->filesystem->expects($this->once())
            ->method('isDirectory')
            ->with('/path/to/cache')
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('isWritable')
            ->with('/path/to/cache')
            ->willReturn(true);

        $subject = new FileBackedContainerDefinitionCache(
            $this->serializer,
            $this->filesystem,
            '/path/to/cache'
        );

        $this->filesystem->expects($this->once())
            ->method('isFile')
            ->with('/path/to/cache/' . $this->cacheKey->asString())
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/path/to/cache/' . $this->cacheKey->asString())
            ->willReturn('my-serialized-container');

        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with('/path/to/cache/' . $this->cacheKey->asString());

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($this->callback(
                fn(SerializedContainerDefinition $serializedContainerDefinition) =>
                    $serializedContainerDefinition->asString() === 'my-serialized-container'
            ))->willThrowException(MismatchedContainerDefinitionSerializerVersions::fromVersionIsNotInstalledAnnotatedContainerVersion('1.0'));

        $actual = $subject->get($this->cacheKey);

        self::assertNull($actual);
    }

    public function testGetWithEmptyFileRemovesOffendingCacheAndReturnsNull() : void {
        $this->filesystem->expects($this->once())
            ->method('isDirectory')
            ->with('/path/to/cache')
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('isWritable')
            ->with('/path/to/cache')
            ->willReturn(true);

        $subject = new FileBackedContainerDefinitionCache(
            $this->serializer,
            $this->filesystem,
            '/path/to/cache'
        );

        $this->filesystem->expects($this->once())
            ->method('isFile')
            ->with('/path/to/cache/' . $this->cacheKey->asString())
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/path/to/cache/' . $this->cacheKey->asString())
            ->willReturn('');

        $this->filesystem->expects($this->once())
            ->method('remove')
            ->with('/path/to/cache/' . $this->cacheKey->asString());

        $this->serializer->expects($this->never())
            ->method('deserialize');

        $actual = $subject->get($this->cacheKey);

        self::assertNull($actual);
    }
}
