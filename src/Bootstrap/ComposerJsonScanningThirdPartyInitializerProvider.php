<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Filesystem\Filesystem;

final class ComposerJsonScanningThirdPartyInitializerProvider implements ThirdPartyInitializerProvider {

    /**
     * @var list<class-string<ThirdPartyInitializer>>|null
     */
    private ?array $initializers = null;

    public function __construct(
        private readonly Filesystem                       $filesystem,
        private readonly PackagesComposerJsonPathProvider $composerJsonPathProvider,
    ) {
    }

    public function thirdPartyInitializers() : array {
        if ($this->initializers === null) {
            $this->initializers = $this->scanVendorDirectoryForInitializers();
        }

        return $this->initializers;
    }

    /**
     * @return list<ThirdPartyInitializer>
     */
    private function scanVendorDirectoryForInitializers() : array {
        $initializers = [];
        foreach ($this->composerJsonPathProvider->composerJsonPaths() as $packageComposerJsonPath) {
            $composerData = json_decode(
                $this->filesystem->read($packageComposerJsonPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $packageInitializers = $composerData['extra']['$annotatedContainer']['initializers'] ?? [];
            foreach ($packageInitializers as $packageInitializer) {
                $initializers[] = new $packageInitializer();
            }
        }
        return $initializers;
    }
}
