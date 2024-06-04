<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use DirectoryIterator;
use SplFileInfo;

final class ComposerJsonScanningThirdPartyInitializerProvider implements ThirdPartyInitializerProvider {

    /**
     * @var list<class-string<ThirdPartyInitializer>>|null
     */
    private ?array $initializers = null;

    public function __construct(
        private readonly BootstrappingDirectoryResolver $resolver
    ) {
    }

    public function thirdPartyInitializerProviders() : array {
        if ($this->initializers === null) {
            $this->initializers = $this->scanVendorDirectoryForInitializers();
            sort($this->initializers);
        }

        return $this->initializers;
    }

    /**
     * @return list<class-string<ThirdPartyInitializer>>
     */
    private function scanVendorDirectoryForInitializers() : array {
        $packages = [];
        $vendorIterator = new DirectoryIterator($this->resolver->vendorPath());
        /** @var SplFileInfo $fileInfo */
        foreach ($vendorIterator as $fileInfo) {
            if (!$fileInfo->isDir() || $fileInfo->isDot()) {
                continue;
            }

            $vendorName = basename($fileInfo->getPathname());
            foreach (new DirectoryIterator($fileInfo->getPathname()) as $vendorPackageInfo) {
                if (!$vendorPackageInfo->isDir() || $vendorPackageInfo->isDot()) {
                    continue;
                }
                $packages[] = sprintf('%s/%s', $vendorName, basename($vendorPackageInfo->getPathname()));
            }
        }

        $initializers = [];
        foreach ($packages as $package) {
            $packageComposerJson = sprintf('%s/%s/composer.json', $this->resolver->vendorPath(), $package);
            $composerData = json_decode(
                file_get_contents($packageComposerJson),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $packageInitializers = $composerData['extra']['$annotatedContainer']['initializers'] ?? [];
            foreach ($packageInitializers as $packageInitializer) {
                $initializers[] = $packageInitializer;
            }
        }
        return $initializers;
    }
}
