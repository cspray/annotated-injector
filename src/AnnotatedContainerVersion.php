<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Composer\InstalledVersions;

final class AnnotatedContainerVersion {

    // @codeCoverageIgnoreStart
    private function __construct() {
    }
    // @codeCoverageIgnoreEnd

    public static function version() : string {
        $version = InstalledVersions::getVersion('cspray/annotated-container');
        // we're trying to find this package, the only supported method of installing annotated-container
        // is through composer. If this really is null then they're doing something with AC that isn't supported
        assert($version !== null);
        return $version;
    }
}
