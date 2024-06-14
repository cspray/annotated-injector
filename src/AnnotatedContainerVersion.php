<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Composer\InstalledVersions;

final class AnnotatedContainerVersion {

    // @codeCoverageIgnoreStart
    private function __construct() {
    }
    // @codeCoverageIgnoreEnd

    public static function version() : string {
        return InstalledVersions::getVersion('cspray/annotated-container');
    }
}
