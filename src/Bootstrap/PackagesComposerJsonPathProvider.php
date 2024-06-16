<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

interface PackagesComposerJsonPathProvider {

    /**
     * @return list<non-empty-string>
     */
    public function composerJsonPaths() : array;
}
