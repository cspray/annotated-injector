<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Composer\InstalledVersions;

final class AnnotatedContainerVersion {

    private function __construct() {
    }

    public static function version() : string {
        return InstalledVersions::getVersion('cspray/annotated-container');
    }
}
