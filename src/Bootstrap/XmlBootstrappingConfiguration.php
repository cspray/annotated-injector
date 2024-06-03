<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\StaticAnalysis\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
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
     * @var list<PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver|ContainerAnalyticsObserver>
     */
    private readonly array $observers;

    public function __construct(
        private readonly string $xmlFile,
        private readonly BootstrappingDirectoryResolver $directoryResolver,
        private readonly ?ParameterStoreFactory $parameterStoreFactory = null,
        private readonly ?ObserverFactory $observerFactory = null,
        private readonly ?DefinitionProviderFactory $definitionProviderFactory = null
    ) {
        if (!file_exists($this->xmlFile)) {
            throw InvalidBootstrapConfiguration::fromFileMissing($this->xmlFile);
        }

        try {
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
                if (isset($this->definitionProviderFactory)) {
                    $definitionProviders[] = $this->definitionProviderFactory->createProvider($definitionProviderType);
                } else {
                    if (!class_exists($definitionProviderType) ||
                        !is_subclass_of($definitionProviderType, DefinitionProvider::class)) {
                        throw InvalidBootstrapConfiguration::fromConfiguredDefinitionProviderWrongType($definitionProviderType);
                    }
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
                    if (isset($this->parameterStoreFactory)) {
                        $parameterStore = $this->parameterStoreFactory->createParameterStore($parameterStoreType);
                    } else {
                        if (!class_exists($parameterStoreType) || !is_subclass_of($parameterStoreType, ParameterStore::class)) {
                            throw InvalidBootstrapConfiguration::fromConfiguredParameterStoreWrongType($parameterStoreType);
                        }
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
                    if (isset($this->observerFactory)) {
                        $observer = $this->observerFactory->createObserver($observerClass);
                    } else {
                        if (!$this->isObserverType($observerClass)) {
                            throw InvalidBootstrapConfiguration::fromConfiguredObserverWrongType($observerClass);
                        }
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
            } elseif ($hasLoggingFile) {
                $loggingFilePath = $this->directoryResolver->getLogPath($loggingFileNodes[0]->nodeValue);
                $logger = new FileLogger($dateTimeProvider, $loggingFilePath);
            } elseif ($hasStdoutFile) {
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

    private function isObserverType(string $observerClass) : bool {
        if (!class_exists($observerClass)) {
            return false;
        }

        return is_subclass_of($observerClass, PreAnalysisObserver::class) ||
            is_subclass_of($observerClass, PostAnalysisObserver::class) ||
            is_subclass_of($observerClass, ContainerCreatedObserver::class) ||
            is_subclass_of($observerClass, ContainerAnalyticsObserver::class);
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

    /**
     * @deprecated
     */
    public function getLogger() : ?LoggerInterface {
        return $this->logger;
    }

    /**
     * @deprecated
     */
    public function getLoggingExcludedProfiles() : array {
        return $this->excludedProfiles;
    }

    /**
     * @return list<PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver|ContainerAnalyticsObserver>
     * @deprecated
     */
    public function getObservers() : array {
        return $this->observers;
    }
}
