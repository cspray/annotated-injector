<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition\Cache;

use Cspray\AnnotatedContainer\StaticAnalysis\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;

final class CacheKey {

    /**
     * @param non-empty-string $key
     */
    private function __construct(
        private readonly string $key
    ) {
    }

    public static function fromContainerDefinitionAnalysisOptions(ContainerDefinitionAnalysisOptions $options) : self {
        $dirs = $options->scanDirectories();
        sort($dirs);
        $key = join($dirs);
        $definitionProvider = $options->definitionProvider();
        if ($definitionProvider !== null) {
            if ($definitionProvider instanceof CompositeDefinitionProvider) {
                $key .= '/' . $definitionProvider;
            } else {
                $key .= '/' . $definitionProvider::class;
            }
        }

        return new self(md5($key));
    }

    /**
     * @return non-empty-string
     */
    public function asString() : string {
        return $this->key;
    }
}
