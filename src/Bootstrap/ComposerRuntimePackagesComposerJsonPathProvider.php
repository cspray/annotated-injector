<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Composer\InstalledVersions;

final class ComposerRuntimePackagesComposerJsonPathProvider implements PackagesComposerJsonPathProvider {

    public function composerJsonPaths() : array {
        $paths = [];
        foreach (InstalledVersions::getInstalledPackages() as $installedPackage) {
            if ($installedPackage === InstalledVersions::getRootPackage()['name']) {
                continue;
            }
            $installPath = InstalledVersions::getInstallPath($installedPackage);
            if ($installPath === null) {
                continue;
            }
            $path = realpath($installPath . '/composer.json');
            assert(is_string($path) && $path !== '');

            $paths[] = $path;
        }
        return $paths;
    }
}
