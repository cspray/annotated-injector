<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainer\Filesystem\PhpFunctionsFilesystem;

final class VendorPresenceBasedBootstrappingDirectoryResolver implements BootstrappingDirectoryResolver {

    private readonly BootstrappingDirectoryResolver $resolver;

    public function __construct(
        Filesystem $filesystem = new PhpFunctionsFilesystem(),
        string $fileDir = __DIR__
    ) {
        $root = dirname($fileDir, 2);
        if (!$filesystem->exists($root . '/vendor/autoload.php')) {
            $root = dirname($fileDir, 5);
        }

        $this->resolver = new RootDirectoryBootstrappingDirectoryResolver($root);
    }

    public function configurationPath(string $subPath = '') : string {
        return $this->resolver->configurationPath($subPath);
    }

    public function rootPath(string $subPath = '') : string {
        return $this->resolver->rootPath($subPath);
    }
}
