<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ArchitecturalDecisions;

use Cspray\ArchitecturalDecision\Initializer as AdrInitializer;

final class Initializer extends AdrInitializer {

    public function getAdditionalScanPaths() : array {
        $root = dirname(__DIR__, 2);
        if (!file_exists($root . '/vendor/autoload.php')) {
            throw new \RuntimeException('This initializer is expected to only be run by Annotated Container directly');
        }

        return [$root . '/vendor/cspray/annotated-container-adr', $root . '/vendor/cspray/annotated-container-attribute'];
    }
}
