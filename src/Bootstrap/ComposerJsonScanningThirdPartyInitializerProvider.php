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
            assert(is_array($composerData));

            $extra = $composerData['extra'] ?? null;
            if (!is_array($extra) || !array_key_exists('$annotatedContainer', $extra)) {
                return [];
            }

            $annotatedContainerExtra = $extra['$annotatedContainer'];

            if (!is_array($annotatedContainerExtra)) {
                throw InvalidThirdPartyInitializer::fromComposerExtraAnnotatedContainerConfigNotArray();
            }

            if (!array_key_exists('initializers', $annotatedContainerExtra)) {
                throw InvalidThirdPartyInitializer::fromComposerExtraAnnotatedContainerConfigNoInitializers();
            }

            $packageInitializers = $annotatedContainerExtra['initializers'];
            if (!is_array($packageInitializers)) {
                throw InvalidThirdPartyInitializer::fromComposerExtraAnnotatedContainerConfigInitializersNotArray();
            }

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
