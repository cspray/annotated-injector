<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Compile\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\Compile\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;
use Cspray\AnnotatedContainer\Internal\CompositeLogger;
use Cspray\AnnotatedContainer\Internal\FileLogger;
use Cspray\AnnotatedContainer\Internal\StdoutLogger;
use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;

use Psr\Log\LoggerInterface;
use function libxml_use_internal_errors;

final class XmlBootstrappingConfiguration implements BootstrappingConfiguration {

    /**
     * @var list<string>
     */
    private readonly array $directories;
    private readonly ?DefinitionProvider $definitionProvider;
    private readonly ?string $cacheDir;
    private readonly ?LoggerInterface $logger;
    private readonly array $excludedProfiles;

    /**
     * @var list<ParameterStore>
     */
    private readonly array $parameterStores;

    /**
     * @var list<Observer>
     */
    private readonly array $observers;

    public function __construct(
        private readonly string $xmlFile,
        private readonly BootstrappingDirectoryResolver $directoryResolver,
        private readonly ?ParameterStoreFactory $parameterStoreFactory = null,
        private readonly ?ObserverFactory $observerFactory = null,
        private readonly ?DefinitionProviderFactory $definitionProviderFactory = null
    ) {
        try{
            $schemaFile = dirname(__DIR__, 2) . '/annotated-container.xsd';
            $dom = new DOMDocument();
            $dom->load($this->xmlFile);
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

                /** @var DOMElement $dir */
                foreach ($dirs as $dir) {
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
                $definitionProviderType = trim($definitionProviderNode->nodeValue);
                if (!class_exists($definitionProviderType) ||
                    !is_subclass_of($definitionProviderType, DefinitionProvider::class)) {
                    throw InvalidBootstrapConfiguration::fromConfiguredDefinitionProviderWrongType();
                }
                if (isset($this->definitionProviderFactory)) {
                    $definitionProviders[] = $this->definitionProviderFactory->createProvider($definitionProviderType);
                } else{
                    $definitionProviders[] = new $definitionProviderType();
                }
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
                    if (!class_exists($parameterStoreType) || !is_subclass_of($parameterStoreType, ParameterStore::class)) {
                        throw InvalidBootstrapConfiguration::fromConfiguredParameterStoreWrongType();
                    }
                    if (isset($this->parameterStoreFactory)) {
                        $parameterStore = $this->parameterStoreFactory->createParameterStore($parameterStoreType);
                    } else {
                        $parameterStore = new $parameterStoreType();
                    }
                    $parameterStores[] = $parameterStore;
                }
            }

            $observers = [];
            $observerNodes = $xpath->query('/ac:annotatedContainer/ac:observers/ac:observer/text()');
            if ($observerNodes instanceof DOMNodeList) {
                foreach ($observerNodes as $observerNode) {
                    $observerClass = (string) $observerNode->nodeValue;
                    if (!class_exists($observerClass) || !is_subclass_of($observerClass, Observer::class)) {
                        throw InvalidBootstrapConfiguration::fromConfiguredObserverWrongType();
                    }
                    if (isset($this->observerFactory)) {
                        $observer = $this->observerFactory->createObserver($observerClass);
                    } else {
                        $observer = new $observerClass();
                    }
                    $observers[] = $observer;
                }
            }

            /** @var DOMNodeList $cacheDirNodes */
            $cacheDirNodes = $xpath->query('/ac:annotatedContainer/ac:cacheDir');
            $cache = null;
            if (count($cacheDirNodes) === 1) {
                $cache = $cacheDirNodes[0]->textContent;
            }

            $loggingFileNodes = $xpath->query('/ac:annotatedContainer/ac:logging/ac:file/text()');
            $loggingStdoutNodes = $xpath->query('/ac:annotatedContainer/ac:logging/ac:stdout');
            $logger = null;

            $hasLoggingFile = $loggingFileNodes instanceof DOMNodeList && count($loggingFileNodes) === 1;
            $hasStdoutFile = $loggingStdoutNodes instanceof DOMNodeList && count($loggingStdoutNodes) === 1;

            $dateTimeProvider = fn() : DateTimeImmutable => new DateTimeImmutable();

            if ($hasLoggingFile && $hasStdoutFile) {
                $loggingFilePath = $this->directoryResolver->getLogPath($loggingFileNodes[0]->nodeValue);
                $fileLogger = new FileLogger($dateTimeProvider, $loggingFilePath);
                $stdoutLogger = new StdoutLogger($dateTimeProvider);
                $logger = new CompositeLogger($fileLogger, $stdoutLogger);
            } else if ($hasLoggingFile) {
                $loggingFilePath = $this->directoryResolver->getLogPath($loggingFileNodes[0]->nodeValue);
                $logger = new FileLogger($dateTimeProvider, $loggingFilePath);
            } else if ($hasStdoutFile) {
                $logger = new StdoutLogger($dateTimeProvider);
            }

            $excludedProfilesNodes = $xpath->query('/ac:annotatedContainer/ac:logging/ac:exclude/ac:profile/text()');
            $excludedProfiles = [];

            if ($excludedProfilesNodes instanceof DOMNodeList) {
                foreach ($excludedProfilesNodes as $node) {
                    $excludedProfiles[] = $node->nodeValue;
                }
            }

            $this->directories = $scanDirectories;
            $this->definitionProvider = $definitionProvider;
            $this->cacheDir = $cache;
            $this->parameterStores = $parameterStores;
            $this->observers = $observers;
            $this->logger = $logger;
            $this->excludedProfiles = $excludedProfiles;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors(false);
        }
    }

    public function getScanDirectories() : array {
        return $this->directories;
    }

    #[SingleEntrypointDefinitionProvider]
    public function getContainerDefinitionProvider() : ?DefinitionProvider {
        return $this->definitionProvider;
    }

    /**
     * @return list<ParameterStore>
     */
    public function getParameterStores() : array {
        return $this->parameterStores;
    }

    public function getCacheDirectory() : ?string {
        return $this->cacheDir;
    }

    public function getLogger() : ?LoggerInterface {
        return $this->logger;
    }

    public function getLoggingExcludedProfiles() : array {
        return $this->excludedProfiles;
    }

    public function getObservers() : array {
        return $this->observers;
    }
}