<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainer\StaticAnalysis\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;

use function libxml_use_internal_errors;

final class XmlBootstrappingConfiguration implements BootstrappingConfiguration {

    /**
     * @var list<string>
     */
    private readonly array $directories;
    private readonly ?DefinitionProvider $definitionProvider;

    /**
     * @var list<ParameterStore>
     */
    private readonly array $parameterStores;

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly string $xmlFile,
        private readonly ParameterStoreFactory $parameterStoreFactory,
        private readonly DefinitionProviderFactory $definitionProviderFactory
    ) {
        if (!$this->filesystem->isFile($this->xmlFile)) {
            throw InvalidBootstrapConfiguration::fromFileMissing($this->xmlFile);
        }

        try {
            $schemaFile = dirname(__DIR__, 2) . '/annotated-container.xsd';
            $dom = new DOMDocument();
            $dom->loadXML($this->filesystem->read($this->xmlFile));
            libxml_use_internal_errors(true);
            if (!$dom->schemaValidate($schemaFile)) {
                throw InvalidBootstrapConfiguration::fromFileDoesNotValidateSchema($this->xmlFile);
            }

            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('ac', 'https://annotated-container.cspray.io/schema/annotated-container.xsd');

            $scanDirectoriesNodes = $xpath->query('/ac:annotatedContainer/ac:scanDirectories/ac:source/ac:dir');
            $scanDirectories = [];
            foreach ($scanDirectoriesNodes as $scanDirectory) {
                $scanDirectories[] = $scanDirectory->textContent;
            }

            $vendorPackagesNodes = $xpath->query('/ac:annotatedContainer/ac:scanDirectories/ac:vendor/ac:package');
            foreach ($vendorPackagesNodes as $vendorPackageNode) {
                assert($vendorPackageNode instanceof DOMElement);

                $name = $vendorPackageNode->getElementsByTagNameNS(
                    'https://annotated-container.cspray.io/schema/annotated-container.xsd',
                    'name'
                )->item(0)?->nodeValue;

                assert($name !== null);

                $source = $vendorPackageNode->getElementsByTagNameNS(
                    'https://annotated-container.cspray.io/schema/annotated-container.xsd',
                    'source'
                )->item(0);

                assert($source instanceof DOMElement);

                $dirs = $source->getElementsByTagNameNS(
                    'https://annotated-container.cspray.io/schema/annotated-container.xsd',
                    'dir'
                );

                foreach ($dirs as $dir) {
                    assert($dir->nodeValue !== null);
                    $vendorScanPath = sprintf(
                        'vendor/%s/%s',
                        trim($name),
                        trim($dir->nodeValue)
                    );
                    $scanDirectories[] = $vendorScanPath;
                }
            }

            $definitionProvider = null;
            $definitionProviderNodes = $xpath->query('/ac:annotatedContainer/ac:definitionProviders/ac:definitionProvider/text()');
            $definitionProviders = [];
            foreach ($definitionProviderNodes as $definitionProviderNode) {
                assert($definitionProviderNode->nodeValue !== null);
                $definitionProviderType = trim($definitionProviderNode->nodeValue);
                $definitionProviders[] = $this->definitionProviderFactory->createProvider($definitionProviderType);
            }

            if ($definitionProviders !== []) {
                $definitionProvider = new CompositeDefinitionProvider(...$definitionProviders);
            }

            $parameterStores = [];
            $parameterStoreNodes = $xpath->query('/ac:annotatedContainer/ac:parameterStores/ac:parameterStore/text()');
            if ($parameterStoreNodes instanceof DOMNodeList) {
                foreach ($parameterStoreNodes as $parameterStoreNode) {
                    assert(isset($parameterStoreNode->nodeValue));
                    $parameterStoreType = trim($parameterStoreNode->nodeValue);
                    $parameterStore = $this->parameterStoreFactory->createParameterStore($parameterStoreType);
                    $parameterStores[] = $parameterStore;
                }
            }

            $this->directories = $scanDirectories;
            $this->definitionProvider = $definitionProvider;
            $this->parameterStores = $parameterStores;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors(false);
        }
    }

    public function scanDirectories() : array {
        return $this->directories;
    }

    #[SingleEntrypointDefinitionProvider]
    public function containerDefinitionProvider() : ?DefinitionProvider {
        return $this->definitionProvider;
    }

    /**
     * @return list<ParameterStore>
     */
    public function parameterStores() : array {
        return $this->parameterStores;
    }

    public function cache() : ?ContainerDefinitionCache {
        return null;
    }
}
