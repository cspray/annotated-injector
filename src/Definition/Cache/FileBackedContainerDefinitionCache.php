<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition\Cache;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Definition\Serializer\SerializedContainerDefinition;
use Cspray\AnnotatedContainer\Definition\Serializer\XmlContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Exception\CacheDirectoryNotFound;
use Cspray\AnnotatedContainer\Exception\CacheDirectoryNotWritable;
use Cspray\AnnotatedContainer\Exception\MismatchedContainerDefinitionSerializerVersions;

final class FileBackedContainerDefinitionCache implements ContainerDefinitionCache {


    /**
     * @param non-empty-string $cacheDir
     */
    public function __construct(
        private readonly ContainerDefinitionSerializer $containerDefinitionSerializer,
        private readonly string $cacheDir
    ) {
        if (!is_dir($this->cacheDir)) {
            throw CacheDirectoryNotFound::fromDirectoryNotFound($this->cacheDir);
        }
        if (!is_writable($this->cacheDir)) {
            throw CacheDirectoryNotWritable::fromDirectoryNotWritable($this->cacheDir);
        }
    }

    public function set(CacheKey $cacheKey, ContainerDefinition $containerDefinition) : void {
        file_put_contents(
            $this->cacheDir . '/' . $cacheKey->asString(),
            $this->containerDefinitionSerializer->serialize($containerDefinition)->asString()
        );
    }

    public function get(CacheKey $cacheKey) : ?ContainerDefinition {
        $filePath = $this->cacheDir . '/' . $cacheKey->asString();
        if (!file_exists($filePath)) {
            return null;
        }

        $serializedContainerDefinition = SerializedContainerDefinition::fromString(file_get_contents($filePath));

        try {
            return $this->containerDefinitionSerializer->deserialize($serializedContainerDefinition);
        } catch (MismatchedContainerDefinitionSerializerVersions) {
            $this->remove($cacheKey);
            return null;
        }
    }

    public function remove(CacheKey $cacheKey) : void {
        if (file_exists($this->cacheDir . '/' . $cacheKey->asString())) {
            unlink($this->cacheDir . '/' . $cacheKey->asString());
        }
    }
}
