<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

abstract class ThirdPartyInitializer {

    final public function __construct() {
    }

    abstract public function getPackageName() : string;

    /**
     * @return list<non-empty-string>
     */
    abstract public function getRelativeScanDirectories() : array;

    /**
     * @return list<non-empty-string>
     * @deprecated
     */
    abstract public function getObserverClasses() : array;

    abstract public function getDefinitionProviderClass() : ?string;
}
