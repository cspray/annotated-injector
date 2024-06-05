<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Exception\InvalidCache;

/**
 * A ContainerDefinitionCompiler decorator that allows for a ContainerDefinition to be serialized and cached to the
 * filesystem; this could potentially save time on very large codebase or be used when building production to not
 * require Container compilation on every request.
 */
final class CacheAwareContainerDefinitionAnalyzer implements ContainerDefinitionAnalyzer {

    /**
     * @param ContainerDefinitionAnalyzer $containerDefinitionCompiler The compiler to use if the cache file is not present
     * @param ContainerDefinitionSerializer $containerDefinitionSerializer The serializer to serialize/deserialize the cached ContainerDefinition
     * @param string $cacheDir The directory that the cache files should be generated
     */
    public function __construct(private readonly ContainerDefinitionAnalyzer $containerDefinitionCompiler, private readonly ContainerDefinitionSerializer $containerDefinitionSerializer, private readonly string $cacheDir) {
    }

    /**
     * Will generate a ContainerDefinition from a serialized cache file.
     *
     * If the cached file is not present will generate a ContainerDefinition from the passed ContainerDefinitionCompiler
     * and save it to the $cacheDir based off of the directories to scan and the active profiles for the given compile
     * options.
     *
     * Please see bin/annotated-container compile --help for more information on pre-generating the cached ContainerDefinition.
     *
     * @param ContainerDefinitionAnalysisOptions $containerDefinitionAnalysisOptions
     * @return ContainerDefinition
     * @throws InvalidCache
     */
    public function analyze(ContainerDefinitionAnalysisOptions $containerDefinitionAnalysisOptions): ContainerDefinition {
        $cacheFile = $this->getCacheFile($containerDefinitionAnalysisOptions->getScanDirectories());
        if (is_file($cacheFile)) {
            $containerDefinition = $this->containerDefinitionSerializer->deserialize(file_get_contents($cacheFile));
            if ($containerDefinition instanceof ContainerDefinition) {
                return $containerDefinition;
            }
        }

        $containerDefinition = $this->containerDefinitionCompiler->analyze($containerDefinitionAnalysisOptions);
        $serialized = $this->containerDefinitionSerializer->serialize($containerDefinition);
        $contentWritten = @file_put_contents($cacheFile, $serialized);
        if ($contentWritten === false) {
            throw InvalidCache::fromUnwritableDirectory($this->cacheDir);
        }
        return $containerDefinition;
    }

    private function getCacheFile(array $dirs) : string {
        sort($dirs);
        return sprintf(
            '%s/%s',
            $this->cacheDir,
            md5(join($dirs))
        );
    }
}
