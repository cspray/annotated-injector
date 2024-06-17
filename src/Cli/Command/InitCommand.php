<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Cli\Command\Service\ConfigFileNameDecider;
use Cspray\AnnotatedContainer\Cli\Exception\ComposerConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\InvalidOptionType;
use Cspray\AnnotatedContainer\Cli\Exception\PotentialConfigurationOverwrite;
use Cspray\AnnotatedContainer\Cli\Input\Input;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Exception\ComposerAutoloadNotFound;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use DOMDocument;
use DOMException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

final class InitCommand implements Command {

    private const XML_SCHEMA = 'https://annotated-container.cspray.io/schema/annotated-container.xsd';

    public function __construct(
        private readonly BootstrappingDirectoryResolver $directoryResolver,
        private readonly ThirdPartyInitializerProvider $initializerProvider,
        private readonly Filesystem $filesystem
    ) {
    }

    public function name() : string {
        return 'init';
    }

    public function summary() : string {
        return 'Setup Annotated Container configuration using a set of common conventions.';
    }

    public function help() : string {
        $summary = $this->summary();
        return <<<SHELL
NAME

    init - $summary
           
SYNOPSIS

    <bold>init</bold> [OPTION]...
    
DESCRIPTION

    <bold>init</bold> ensures Annotated Container is bootstrapped from a configuration 
    file correctly. Each step corresponds to its own option to override the 
    default values. The option is briefly discussed here and reviewed in more 
    detail in the OPTIONS section below. Be sure to review the Resolving File Paths 
    and Defining Class Configuration below. 
    
    Steps
    ============================================================================
    
    1. Create a configuration file that stores information about how to create 
       your project's Container. This file is named "annotated-container.xml" and 
       created in the root of your project. For more details about the format of 
       this file you can review the schema at 
       https://annotated-container.cspray.io/schemas/annotated-container.xsd.
       
       This command will NEVER overwrite a file that already exists. If the 
       file does exist an error will be thrown. If you're trying to recreate the 
       configuration you'll need to rename or move the existing file first.
       
    2. Setup configuration to scan directories defined by your Composer autoload 
       and autoload-dev configurations. For example, if you have a "composer.json" 
       that resembles the following:
       
       {
         "autoload": {
           "psr-4": {
             "Namespace\\": ["src", "lib"]
           }
         },
         "autoload-dev": {
           "psr-4": {
             "Namespace\\": ["test"]
           }
         }
       }
       
       The directories that would be included in the configuration are "src", 
       "lib", and "test". We'll look for these directories in the root 
       of your project.

    3. Setup configuration to include a DefinitionProvider when you need to 
       configure third-party services. You can provide a single --definition-provider 
       option when executing this command to define configured value. The value
       passed to this option MUST be a fully-qualified class name. By default, 
       no provider will be defined unless an option is passed. If you use this 
       configuration option please review Defining Class Configurations below.
       
    4. Setup configuration to include ParameterStore implementations in the 
       ContainerFactory. You can provide multiple --parameter-store options when 
       executing this command to define configured values. The value passed to 
       this option MUST be a fully-qualified class name. By default, no stores 
       will be defined unless options are passed. If you use this configuration 
       option please review Defining Class Configurations detailed below.
       
    Resolving File Paths
    ============================================================================
    
    There are several values in the generated configuration file that represent 
    only partial values. Before they can be used in Annotated Container they need 
    to be resolved to a full path. This responsibility is taken care of with an 
    implementation of BootstrappingDirectoryResolver. The default implementation 
    will look for all files and directories in the root of your project. If 
    possible, it is recommended to follow this convention. If you can't follow 
    this convention you can implement your own instance and pass it to the 
    Bootstrap constructor and have complete control of the absolute paths used 
    to create your Container.
    
    Defining Class Configurations
    ============================================================================
    
    By default, any class you define in a configuration must be a fully-qualified 
    class name with an empty constructor. If you require constructor dependencies, 
    or can't provide the entire class name for some reason, you can override the 
    corresponding factory implementation passed to the Bootstrap constructor.
   
OPTIONS

    --definition-provider="Fully\Qualified\Class\Name"
    
        Add a DefinitionProvider when generating your Annotated Container. This 
        is primarily used to add third-party services to your Container that 
        can't be annotated. Please be sure to review Defining Class Configurations 
        if you use this value. This option can only be defined 1 time.

    --parameter-store="Fully\Qualified\Class\Name"
    
        Add a ParameterStore to the ContainerFactory. This can be used to allow 
        injecting custom values with the Inject Attribute. Please be sure to 
        review Defining Class Configurations if you use this value.
    
SHELL;
    }

    /**
     * @throws InvalidOptionType
     * @throws ComposerConfigurationNotFound
     * @throws PotentialConfigurationOverwrite
     * @throws DOMException
     */
    public function handle(Input $input, TerminalOutput $output) : int {
        $this->validateInput($input);

        $configFile = $this->directoryResolver->configurationPath('annotated-container.xml');
        if ($this->filesystem->exists($configFile)) {
            throw new PotentialConfigurationOverwrite(
                'The configuration file "annotated-container.xml" is already present and cannot be overwritten.'
            );
        }

        $composerFile = $this->directoryResolver->rootPath('composer.json');
        if (!$this->filesystem->exists($composerFile)) {
            throw ComposerConfigurationNotFound::fromMissingComposerJson();
        }


        // Normally we'd want to test that this is what we expect. However, if you have a composer.json file we
        // expect it to adhere to the composer.json schema and this is how the relevant pieces of autoloading work.
        // Testing that this piece is formatted correctly is a waste. It is covered by Composer's spec and how
        // Composer autoloading works. If you've messed up this portion of your composer.json chances are this code
        // will never run in the first place.
        /**
         * @var array{
         *     autoload?: array{"psr-0"?: list<non-empty-string>, "psr-4"?: list<non-empty-string>},
         *     "autoload-dev"?: array{"psr-0"?: list<non-empty-string>, "psr-4"?: list<non-empty-string>}
         * } $composer
         */
        $composer = json_decode($this->filesystem->read($composerFile), associative: true, flags: JSON_THROW_ON_ERROR);

        $this->generateAndSaveConfiguration($input, $composer, $configFile);

        $output->stdout->write('<fg:green>Annotated Container initialized successfully!</fg:green>');
        $output->stdout->br();
        $output->stdout->write('Be sure to review the configuration file created in "annotated-container.xml"!');

        return 0;
    }

    /**
     * @throws InvalidOptionType
     */
    private function validateInput(Input $input) : void {
        $definitionProvider = $input->option('definition-provider');
        if (is_bool($definitionProvider)) {
            throw InvalidOptionType::fromBooleanOption('definition-provider');
        } elseif (is_array($definitionProvider)) {
            throw InvalidOptionType::fromArrayOption('definition-provider');
        }

        $parameterStore = $input->option('parameter-store');
        if (is_bool($parameterStore)) {
            throw InvalidOptionType::fromBooleanOption('parameter-store');
        }
    }

    /**
     * @param array{
     *      autoload?: array{"psr-4"?: list<non-empty-string>, "psr-0"?: list<non-empty-string>},
     *      "autoload-dev"?: array{"psr-4"?: list<non-empty-string>, "psr-0"?: list<non-empty-string>}
     *  } $composer
     * @return list<string>
     */
    private function getComposerDirectories(array $composer) : array {
        $normalizedData = $this->normalizedComposerJson($composer);

        $dirs = [];
        $composerDirectories = [
            ...$normalizedData['autoload']['psr-0'],
            ...$normalizedData['autoload']['psr-4'],
            ...$normalizedData['autoload-dev']['psr-0'],
            ...$normalizedData['autoload-dev']['psr-4'],
        ];

        /**
         * @var non-empty-string $composerDir
         */
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($composerDirectories)) as $composerDir) {
            $dirs[] = $composerDir;
        }

        return $dirs;
    }

    /**
     * @param Input $input
     * @param array{
     *      autoload?: array{"psr-4"?: list<non-empty-string>, "psr-0"?: list<non-empty-string>},
     *      "autoload-dev"?: array{"psr-4"?: list<non-empty-string>, "psr-0"?: list<non-empty-string>}
     *  } $composer
     * @param string $configFile
     * @return void
     * @throws ComposerAutoloadNotFound
     * @throws DOMException
     */
    private function generateAndSaveConfiguration(Input $input, array $composer, string $configFile) : void {
        $composerDirectories = $this->getComposerDirectories($composer);
        if ($composerDirectories === []) {
            throw ComposerAutoloadNotFound::fromMissingAutoload();
        }
        $dom = new DOMDocument(version: '1.0', encoding: 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->appendChild($dom->createElementNS(self::XML_SCHEMA, 'annotatedContainer'));
        $root->setAttribute('version', AnnotatedContainerVersion::version());

        $scanDirectories = $root->appendChild($dom->createElementNS(self::XML_SCHEMA, 'scanDirectories'));
        $source = $scanDirectories->appendChild($dom->createElementNS(self::XML_SCHEMA, 'source'));

        foreach ($composerDirectories as $composerDirectory) {
            $dirNode = $dom->createElementNS(self::XML_SCHEMA, 'dir', $composerDirectory);
            $source->appendChild($dirNode);
        }

        $definitionProvidersNode = $root->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'definitionProviders')
        );
        /** @var string|null $definitionProvider */
        $definitionProvider = $input->option('definition-provider');
        if (isset($definitionProvider)) {
            $definitionProvidersNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'definitionProvider', $definitionProvider)
            );
        }

        $parameterStoresInput = $input->option('parameter-store');
        if ($parameterStoresInput !== null) {
            $parameterStores = is_string($parameterStoresInput) ? [$parameterStoresInput] : $parameterStoresInput;
            $parameterStoresNode = $root->appendChild($dom->createElementNS(self::XML_SCHEMA, 'parameterStores'));

            assert(is_array($parameterStores));

            /** @var non-empty-string $parameterStore */
            foreach ($parameterStores as $parameterStore) {
                $parameterStoresNode->appendChild($dom->createElementNS(self::XML_SCHEMA, 'parameterStore', $parameterStore));
            }
        }

        $vendor = $scanDirectories->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'vendor')
        );
        foreach ($this->initializerProvider->thirdPartyInitializers() as $thirdPartyInitializer) {
            $packageRelativeScanDirectories = $thirdPartyInitializer->relativeScanDirectories();
            if (count($packageRelativeScanDirectories) > 0) {
                $package = $vendor->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'package')
                );
                $package->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'name', $thirdPartyInitializer->packageName())
                );
                $packageSource = $package->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'source')
                );

                foreach ($packageRelativeScanDirectories as $packageRelativeScanDirectory) {
                    $packageSource->appendChild(
                        $dom->createElementNS(self::XML_SCHEMA, 'dir', $packageRelativeScanDirectory)
                    );
                }
            }

            $providerClass = $thirdPartyInitializer->definitionProviderClass();
            if ($providerClass !== null) {
                $definitionProvidersNode->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'definitionProvider', $providerClass)
                );
            }
        }

        $schemaPath = dirname(__DIR__, 3) . '/annotated-container.xsd';
        $dom->schemaValidate($schemaPath);
        $this->filesystem->write($configFile, $dom->saveXML());
    }

    /**
     * @param array{
     *     autoload?: array{"psr-4"?: list<non-empty-string>, "psr-0"?: list<non-empty-string>},
     *     "autoload-dev"?: array{"psr-4"?: list<non-empty-string>, "psr-0"?: list<non-empty-string>}
     * } $composer
     * @return array{
     *      autoload: array{"psr-4": list<non-empty-string>, "psr-0": list<non-empty-string>},
     *     "autoload-dev": array{"psr-4": list<non-empty-string>, "psr-0": list<non-empty-string>}
     *  }
     */
    private function normalizedComposerJson(array $composer) : array {
        /**
         * @var array{
         *      autoload: array{"psr-4": list<non-empty-string>, "psr-0": list<non-empty-string>},
         *      "autoload-dev": array{"psr-4": list<non-empty-string>, "psr-0": list<non-empty-string>}
         *  } $normalized
         */
        $normalized = array_merge_recursive(
            [
                'autoload' => [
                    'psr-4' => [],
                    'psr-0' => []
                ],
                'autoload-dev' => [
                    'psr-4' => [],
                    'psr-0' => []
                ]
            ],
            $composer
        );

        return $normalized;
    }
}
