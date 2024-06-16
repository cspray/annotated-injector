<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainerFixture\Fixtures;

final class FixtureBootstrappingDirectoryResolver implements BootstrappingDirectoryResolver {

    public function configurationPath(string $subPath = '') : string {
        return sprintf('vfs://root/%s', $subPath);
    }

    public function rootPath(string $subPath = '') : string {
        return sprintf('%s/%s', Fixtures::getRootPath(), $subPath);
    }
}
