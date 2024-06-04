<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

interface BootstrappingDirectoryResolver {

    public function getConfigurationPath(string $subPath) : string;

    public function getPathFromRoot(string $subPath) : string;

    public function getCachePath(string $subPath) : string;

    public function getVendorPath() : string;
}
