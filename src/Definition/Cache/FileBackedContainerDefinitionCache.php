<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition\Cache;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Definition\Serializer\SerializedContainerDefinition;
use Cspray\AnnotatedContainer\Definition\Serializer\XmlContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Exception\CacheDirectoryNotFound;
use Cspray\AnnotatedContainer\Exception\CacheDirectoryNotWritable;
use Cspray\AnnotatedContainer\Exception\MismatchedContainerDefinitionSerializerVersions;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;

final class FileBackedContainerDefinitionCache implements ContainerDefinitionCache {


    /**
     * @param non-empty-string $cacheDir
     */
    public function __construct(
        private readonly ContainerDefinitionSerializer $containerDefinitionSerializer,
        private readonly Filesystem $filesystem,
        private readonly string $cacheDir
    ) {
        if (!$this->filesystem->isDirectory($this->cacheDir)) {
            throw CacheDirectoryNotFound::fromDirectoryNotFound($this->cacheDir);
        }
        if (!$this->filesystem->isWritable($this->cacheDir)) {
            throw CacheDirectoryNotWritable::fromDirectoryNotWritable($this->cacheDir);
        }
    }

    public function set(CacheKey $cacheKey, ContainerDefinition $containerDefinition) : void {
        $this->filesystem->write(
            $this->cachePath($cacheKey),
            $this->containerDefinitionSerializer->serialize($containerDefinition)->asString()
        );
    }

    public function get(CacheKey $cacheKey) : ?ContainerDefinition {
        $filePath = $this->cachePath($cacheKey);
        if (!$this->filesystem->isFile($filePath)) {
            return null;
        }

        $contents = $this->filesystem->read($filePath);
        if ($contents === '') {
            $this->remove($cacheKey);
            return null;
        }

        try {
            return $this->containerDefinitionSerializer->deserialize(
                SerializedContainerDefinition::fromString($contents)
            );
        } catch (MismatchedContainerDefinitionSerializerVersions) {
            $this->remove($cacheKey);
            return null;
        }
    }

    public function remove(CacheKey $cacheKey) : void {
        $this->filesystem->remove($this->cachePath($cacheKey));
    }

    /**
     * @param CacheKey $cacheKey
     * @return non-empty-string
     */
    private function cachePath(CacheKey $cacheKey) : string {
        return sprintf(
            '%s/%s',
            $this->cacheDir,
            $cacheKey->asString()
        );
    }
}
