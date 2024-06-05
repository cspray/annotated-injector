<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

interface BootstrappingDirectoryResolver {

    public function configurationPath(string $subPath) : string;

    public function pathFromRoot(string $subPath) : string;

    public function cachePath(string $subPath) : string;

    public function vendorPath() : string;
}
