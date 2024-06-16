<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

final class RootDirectoryBootstrappingDirectoryResolver implements BootstrappingDirectoryResolver {

    public function __construct(
        private readonly string $rootDir
    ) {
    }

    public function configurationPath(string $subPath = '') : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }

    public function rootPath(string $subPath = '') : string {
        return sprintf('%s/%s', $this->rootDir, $subPath);
    }
}
