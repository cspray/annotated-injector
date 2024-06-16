<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Exception\InvalidThirdPartyInitializer;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;

final class ComposerJsonScanningThirdPartyInitializerProvider implements ThirdPartyInitializerProvider {

    /**
     * @var list<ThirdPartyInitializer>
     */
    private readonly array $initializers;

    public function __construct(
        private readonly Filesystem                       $filesystem,
        private readonly PackagesComposerJsonPathProvider $composerJsonPathProvider,
    ) {
        $this->initializers = $this->scanVendorDirectoryForInitializers();
    }

    public function thirdPartyInitializers() : array {
        return $this->initializers;
    }

    /**
     * @return list<ThirdPartyInitializer>
     */
    private function scanVendorDirectoryForInitializers() : array {
        /** @var list<ThirdPartyInitializer> $initializers */
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
                if (!is_string($packageInitializer) || !class_exists($packageInitializer)) {
                    throw InvalidThirdPartyInitializer::fromConfiguredProviderNotClass(
                        $packageComposerJsonPath,
                        $packageInitializer
                    );
                }

                if (!is_a($packageInitializer, ThirdPartyInitializer::class, true)) {
                    throw InvalidThirdPartyInitializer::fromConfiguredProviderNotThirdPartyInitializer(
                        $packageComposerJsonPath,
                        $packageInitializer
                    );
                }

                $initializers[] = new $packageInitializer();
            }
        }
        return $initializers;
    }
}
