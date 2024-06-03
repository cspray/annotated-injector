<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use PackageVersions\Versions;

final class AnnotatedContainerVersion {

    private function __construct() {
    }

    public static function getVersion() : string {
        return Versions::getVersion('cspray/annotated-container');
    }
}
