<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

interface PackagesComposerJsonPathProvider {

    public function composerJsonPaths() : array;

}