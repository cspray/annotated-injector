<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Cli\Command;
use Cspray\AnnotatedContainer\Cli\Exception\InvalidOptionType;
use Cspray\AnnotatedContainer\Cli\Exception\ComposerConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\PotentialConfigurationOverwrite;
use Cspray\AnnotatedContainer\Cli\Input;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\Exception\ComposerAutoloadNotFound;
use DOMDocument;
use DOMException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use function DI\get;

final class InitCommand implements Command {

    private const XML_SCHEMA = 'https://annotated-container.cspray.io/schema/annotated-container.xsd';

    public function __construct(
        private readonly BootstrappingDirectoryResolver $directoryResolver,
        private readonly ThirdPartyInitializerProvider $initializerProvider
    ) {
    }

    public function getName() : string {
        return 'init';
    }

    public function getHelp() : string {
        return <<<SHELL
NAME

    init - Setup bootstrapping of Annotated Container with a configuration file.
           
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
       your project's Container. By default, this file is created in the root of 
       your project. For more details about the format of this file you can review 
       the schema at https://annotated-container.cspray.io/schemas/annotated-container.xsd.
       
       There are 2 primary ways to control the naming of this file. The first 
       is to pass a --config-file option when running this command. The second 
       is to define a key "extra.annotatedContainer.configFile" in your 
       composer.json that defines the name of the configuration file. If neither 
       of these values are provided the name of the file will default to 
       annotated-container.xml. The order of precedence for naming:
       
       A. The --config-file option if passed
       B. The composer.json "extra.annotatedContainer.configFile" if present
       C. The default value "annotated-container.xml"
       
       This command will NEVER overwrite a file that already exists. If the 
       file does exist an error will be thrown. If you're trying to recreate the 
       configuration you'll need to rename or move the existing file first.
       
       !! Notice !!

       The location of the configuration file is the only value recognized from 
       the composer.json extra configuration. Any other keys present in 
       "extra.annotatedContainer" will be ignored.

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
       "lib", and "test". By default we'll look for these directories in the root 
       of your project. If you need to scan directories outside your project 
       root please review the Caveats & Other Concerns detailed below.
       
    3. Setup configuration to cache your ContainerDefinition in a directory in 
       the root of your project. If this directory doesn't already exist it will
       be created. You can change the directory that is used for caching by passing 
       the --cache-dir option when executing this command. This command will always 
       enable caching. Caching your ContainerDefinition is HIGHLY recommended as 
       statically analysing your codebase can be quite costly. If you need to 
       disable caching for some reason, you're in early development and services 
       are likely to change frequently, you can remove this configuration.

    4. Setup configuration to include a DefinitionProvider when you need to 
       configure third-party services. You can provide a single --definition-provider 
       option when executing this command to define configured value. The value
       passed to this option MUST be a fully-qualified class name. By default, 
       no provider will be defined unless an option is passed. If you use this 
       configuration option please review Defining Class Configurations below.
       
    5. Setup configuration to include ParameterStore implementations in the 
       ContainerFactory. You can provide multiple --parameter-store options when 
       executing this command to define configured values. The value passed to 
       this option MUST be a fully-qualified class name. By default, no stores 
       will be defined unless options are passed. If you use this configuration 
       option please review Defining Class Configurations detailed below.
       
    6. Setup configuration to include Observer implementations to respond to 
       events that happen during Annotated Container's bootstrapping. You can 
       provide multiple --observer options when executing this command to 
       define configured values. The value passed to this option MUST be a 
       fully-qualified class name. By default, no observers will be defined 
       unless options are passed. If you use this configuration option please 
       review Defining Class Configurations detailed below.
       
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

    --cache-dir="cache/dir"
    
        Specify the directory that ContainerDefinition will be cached in. If this
        option is not provided the cache directory will be ".annotated-container-cache".
        This option can only be defined 1 time.
    
    --config-file="file-path.xml"
    
        Set the name of the configuration file that is created. If this option 
        is not provided the config file will default to "annotated-container.xml".
        This option can only be defined 1 time.
        
    --definition-provider="Fully\Qualified\Class\Name"
    
        Add a DefinitionProvider when generating your Annotated Container. This 
        is primarily used to add third-party services to your Container that 
        can't be annotated. Please be sure to review Defining Class Configurations 
        if you use this value. This option can only be defined 1 time.

    --parameter-store="Fully\Qualified\Class\Name"
    
        Add a ParameterStore to the ContainerFactory. This can be used to allow 
        injecting custom values with the Inject Attribute. Please be sure to 
        review Defining Class Configurations if you use this value.
    
    --observer="Fully\Qualified\Class\Name"

        Add an Observer to the bootstrapping process. This can be used to respond 
        to events during the compilation and creation of your Container.

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

        $composerFile = $this->directoryResolver->getConfigurationPath('composer.json');
        if (!file_exists($composerFile)) {
            throw ComposerConfigurationNotFound::fromMissingComposerJson();
        }

        $composer = json_decode(file_get_contents($composerFile), true);

        /** @var string|null $configName */
        $configName = $input->getOption('config-file');
        if (!isset($configName)) {
            $configName = $composer['extra']['annotatedContainer']['configFile'] ?? 'annotated-container.xml';
        }

        $configFile = $this->directoryResolver->getConfigurationPath($configName);
        if (file_exists($configFile)) {
            throw new PotentialConfigurationOverwrite(
                sprintf('The configuration file "%s" is already present and cannot be overwritten.', $configName)
            );
        }

        $this->generateAndSaveConfiguration($input, $composer, $configFile);

        /** @var ?string $cacheDirOpt */
        $cacheDirOpt = $input->getOption('cache-dir');
        $cacheDir = $this->directoryResolver->getCachePath(
            $cacheDirOpt ?? '.annotated-container-cache'
        );
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, recursive: true);
        }

        $output->stdout->write('<fg:green>Annotated Container initialized successfully!</fg:green>');
        $output->stdout->br();
        $output->stdout->write(sprintf('Be sure to review the configuration file created in "%s"!', $configName));

        return 0;
    }

    /**
     * @throws InvalidOptionType
     */
    private function validateInput(Input $input) : void {
        $configFile = $input->getOption('config-file');
        if (is_bool($configFile)) {
            throw InvalidOptionType::fromBooleanOption('config-file');
        } elseif (is_array($configFile)) {
            throw InvalidOptionType::fromArrayOption('config-file');
        }

        $cacheDir = $input->getOption('cache-dir');
        if (is_bool($cacheDir)) {
            throw InvalidOptionType::fromBooleanOption('cache-dir');
        } elseif (is_array($cacheDir)) {
            throw InvalidOptionType::fromArrayOption('cache-dir');
        }

        $definitionProvider = $input->getOption('definition-provider');
        if (is_bool($definitionProvider)) {
            throw InvalidOptionType::fromBooleanOption('definition-provider');
        } elseif (is_array($definitionProvider)) {
            throw InvalidOptionType::fromArrayOption('definition-provider');
        }

        $parameterStore = $input->getOption('parameter-store');
        if (is_bool($parameterStore)) {
            throw InvalidOptionType::fromBooleanOption('parameter-store');
        }

        $observers = $input->getOption('observer');
        if (is_bool($observers)) {
            throw InvalidOptionType::fromBooleanOption('observer');
        }
    }

    /**
     * @return list<string>
     */
    private function getComposerDirectories(array $composer) : array {
        $autoloadPsr4 = $composer['autoload']['psr-4'] ?? [];
        $autoloadPsr0 = $composer['autoload']['psr-0'] ?? [];
        $autoloadDevPsr4 = $composer['autoload-dev']['psr-4'] ?? [];
        $autoloadDevPsr0 = $composer['autoload-dev']['psr-0'] ?? [];

        $composerDirs = [
            ...$autoloadPsr0,
            ...$autoloadPsr4,
        ];
        $composerDevDirs = [
            ...$autoloadDevPsr0,
            ...$autoloadDevPsr4,
        ];

        $dirs = [];

        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($composerDirs)) as $composerDir) {
            $dirs[] = (string) $composerDir;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($composerDevDirs)) as $composerDevDir) {
            $dirs[] = (string) $composerDevDir;
        }

        return $dirs;
    }

    private function generateAndSaveConfiguration(Input $input, array $composer, string $configFile) : void {
        $composerDirectories = $this->getComposerDirectories($composer);
        if ($composerDirectories === []) {
            throw ComposerAutoloadNotFound::fromMissingAutoload();
        }
        $dom = new DOMDocument(version: '1.0', encoding: 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->appendChild($dom->createElementNS(self::XML_SCHEMA, 'annotatedContainer'));

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
        $definitionProvider = $input->getOption('definition-provider');
        if (isset($definitionProvider)) {
            $definitionProvidersNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'definitionProvider', $definitionProvider)
            );
        }

        /** @var string|array|null $parameterStores */
        $parameterStores = $input->getOption('parameter-store');
        if ($parameterStores !== null) {
            $parameterStores = is_string($parameterStores) ? [$parameterStores] : $parameterStores;
            $parameterStoresNode = $root->appendChild($dom->createElementNS(self::XML_SCHEMA, 'parameterStores'));
            foreach ($parameterStores as $parameterStore) {
                $parameterStoresNode->appendChild($dom->createElementNS(self::XML_SCHEMA, 'parameterStore', $parameterStore));
            }
        }

        $vendor = $scanDirectories->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'vendor')
        );
        foreach ($this->initializerProvider->getThirdPartyInitializers() as $thirdPartyInitializerClass) {
            $thirdPartyInitializer = new $thirdPartyInitializerClass();
            $packageRelativeScanDirectories = $thirdPartyInitializer->getRelativeScanDirectories();
            if (count($packageRelativeScanDirectories) > 0) {
                $package = $vendor->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'package')
                );
                $package->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'name', $thirdPartyInitializer->getPackageName())
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

            $providerClass = $thirdPartyInitializer->getDefinitionProviderClass();
            if ($providerClass !== null) {
                $definitionProvidersNode->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'definitionProvider', $providerClass)
                );
            }
        }

        /** @var string|null $cacheDirOpt */
        $cacheDirOpt = $input->getOption('cache-dir');
        $cacheName = $cacheDirOpt ?? '.annotated-container-cache';

        $root->appendChild($dom->createElementNS(self::XML_SCHEMA, 'cacheDir', $cacheName));

        $schemaPath = dirname(__DIR__, 3) . '/annotated-container.xsd';
        $dom->schemaValidate($schemaPath);
        $dom->save($configFile);
    }
}
