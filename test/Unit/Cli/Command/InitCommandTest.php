<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Cli\Command\InitCommand;
use Cspray\AnnotatedContainer\Cli\Exception\ComposerConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\InvalidOptionType;
use Cspray\AnnotatedContainer\Cli\Exception\PotentialConfigurationOverwrite;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Exception\ComposerAutoloadNotFound;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubInput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InitCommandTest extends TestCase {

    private MockObject&BootstrappingDirectoryResolver $directoryResolver;
    private MockObject&ThirdPartyInitializerProvider $thirdPartyInitializerProvider;
    private MockObject&Filesystem $filesystem;
    private InMemoryOutput $stdout;
    private InMemoryOutput $stderr;

    private TerminalOutput $output;
    private InitCommand $subject;

    protected function setUp() : void {
        $this->directoryResolver = $this->createMock(BootstrappingDirectoryResolver::class);
        $this->thirdPartyInitializerProvider = $this->createMock(ThirdPartyInitializerProvider::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->subject = new InitCommand(
            $this->directoryResolver,
            $this->thirdPartyInitializerProvider,
            $this->filesystem
        );
        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->output = new TerminalOutput($this->stdout, $this->stderr);
    }

    public function testGetName() : void {
        self::assertSame('init', $this->subject->name());
    }

    public function testSummary() : void {
        self::assertSame(
            'Setup Annotated Container configuration using a set of common conventions.',
            $this->subject->summary()
        );
    }

    public function testGetHelp() : void {
        $expected = <<<SHELL
NAME

    init - Setup Annotated Container configuration using a set of common conventions.
           
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

        self::assertSame($expected, $this->subject->help());
    }

    public function testInitIfConfigurationExistsThrowsException() : void {
        $stubInput = new StubInput([], ['init']);

        $this->directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/config/dir/annotated-container.xml');

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with('/config/dir/annotated-container.xml')
            ->willReturn(true);

        $this->expectException(PotentialConfigurationOverwrite::class);
        $this->expectExceptionMessage(
            'The configuration file "annotated-container.xml" is already present and cannot be overwritten.'
        );

        $this->subject->handle($stubInput, $this->output);
    }

    public function testInitDefaultFileNoComposerJsonFound() : void {
        $this->directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/config/dir/annotated-container.xml');

        $this->directoryResolver->expects($this->once())
            ->method('rootPath')
            ->with('composer.json')
            ->willReturn('/root/composer.json');

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                ['/config/dir/annotated-container.xml', false],
                ['/root/composer.json', false]
            ]);

        $this->expectException(ComposerConfigurationNotFound::class);
        $this->expectExceptionMessage(
            'The file "composer.json" does not exist and is expected to be found.'
        );

        $stubInput = new StubInput([], ['init']);
        $this->subject->handle($stubInput, $this->output);
    }

    public function testInitDefaultComposerJsonHasNoAutoload() : void {
        $this->directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/config/dir/annotated-container.xml');

        $this->directoryResolver->expects($this->once())
            ->method('rootPath')
            ->with('composer.json')
            ->willReturn('/root/composer.json');

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                ['/config/dir/annotated-container.xml', false],
                ['/root/composer.json', true]
            ]);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/root/composer.json')
            ->willReturn(json_encode([
                'autoload' => [
                ],
                'autoload-dev' => [
                ]
            ], JSON_THROW_ON_ERROR));

        $input = new StubInput([], ['init']);

        $this->expectException(ComposerAutoloadNotFound::class);
        $this->expectExceptionMessage('Did not find any directories to scan based on composer autoload configuration. Please ensure there is a PSR-4 or PSR-0 autoload or autoload-dev set in your composer.json and try again.');
        $this->subject->handle($input, $this->output);
    }

    public function testInitDefaultFileComposerJsonPresentCreatesConfigurationFile() : void {
        $this->directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/config/dir/annotated-container.xml');

        $this->directoryResolver->expects($this->once())
            ->method('rootPath')
            ->with('composer.json')
            ->willReturn('/root/composer.json');

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                ['/config/dir/annotated-container.xml', false],
                ['/root/composer.json', true]
            ]);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/root/composer.json')
            ->willReturn(json_encode([
                'autoload' => [
                    'psr-0' => [
                        'Namespace\\' => 'src'
                    ],
                    'psr-4' => [
                        'Another\\Namespace\\' => ['lib', 'trunk']
                    ]
                ],
                'autoload-dev' => [
                    'psr-4' => [
                        'Namespace\\Test\\' => ['test']
                    ]
                ]
            ], JSON_THROW_ON_ERROR));

        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="$version">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>lib</dir>
      <dir>trunk</dir>
      <dir>test</dir>
    </source>
    <vendor/>
  </scanDirectories>
  <definitionProviders/>
</annotatedContainer>

XML;
        $this->filesystem->expects($this->once())
            ->method('write')
            ->with('/config/dir/annotated-container.xml', $expected);

        $input = new StubInput([], ['init']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
    }

    public function testThirdPartyInitializerProvidersAreAddedToConfiguration() : void {
        $this->directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/config/dir/annotated-container.xml');

        $this->directoryResolver->expects($this->once())
            ->method('rootPath')
            ->with('composer.json')
            ->willReturn('/root/composer.json');

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                ['/config/dir/annotated-container.xml', false],
                ['/root/composer.json', true]
            ]);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/root/composer.json')
            ->willReturn(json_encode([
                'autoload' => [
                    'psr-4' => [
                        'Another\\Namespace\\' => ['src']
                    ]
                ],
                'autoload-dev' => [
                    'psr-4' => [
                        'Namespace\\Test\\' => ['test']
                    ]
                ]
            ], JSON_THROW_ON_ERROR));

        $thirdPartyInitializer = $this->createMock(ThirdPartyInitializer::class);
        $thirdPartyInitializer->expects($this->once())
            ->method('packageName')
            ->willReturn('cspray/package-name');
        $thirdPartyInitializer->expects($this->once())
            ->method('relativeScanDirectories')
            ->willReturn(['src', 'lib']);
        $thirdPartyInitializer->expects($this->once())
            ->method('definitionProviderClass')
            ->willReturn(StubDefinitionProvider::class);

        $this->thirdPartyInitializerProvider->expects($this->once())
            ->method('thirdPartyInitializers')
            ->willReturn([$thirdPartyInitializer]);

        $version = AnnotatedContainerVersion::version();
        $definitionProvider = StubDefinitionProvider::class;
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="$version">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>test</dir>
    </source>
    <vendor>
      <package>
        <name>cspray/package-name</name>
        <source>
          <dir>src</dir>
          <dir>lib</dir>
        </source>
      </package>
    </vendor>
  </scanDirectories>
  <definitionProviders>
    <definitionProvider>$definitionProvider</definitionProvider>
  </definitionProviders>
</annotatedContainer>

XML;

        $this->filesystem->expects($this->once())
            ->method('write')
            ->with('/config/dir/annotated-container.xml', $expected);

        $input = new StubInput([], ['init']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
    }

    public function testSingleDefinitionProviderRespected() : void {
        $this->directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/config/dir/annotated-container.xml');

        $this->directoryResolver->expects($this->once())
            ->method('rootPath')
            ->with('composer.json')
            ->willReturn('/root/composer.json');

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                ['/config/dir/annotated-container.xml', false],
                ['/root/composer.json', true]
            ]);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/root/composer.json')
            ->willReturn(json_encode([
                'autoload' => [
                    'psr-4' => [
                        'Another\\Namespace\\' => ['src']
                    ]
                ],
            ], JSON_THROW_ON_ERROR));

        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="$version">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
    <vendor/>
  </scanDirectories>
  <definitionProviders>
    <definitionProvider>ConsumerClass</definitionProvider>
  </definitionProviders>
</annotatedContainer>

XML;

        $this->filesystem->expects($this->once())
            ->method('write')
            ->with('/config/dir/annotated-container.xml', $expected);

        $input = new StubInput(['definition-provider' => 'ConsumerClass'], ['init']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
    }

    public function testSingleParameterStoreRespected() : void {
        $this->directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/config/dir/annotated-container.xml');

        $this->directoryResolver->expects($this->once())
            ->method('rootPath')
            ->with('composer.json')
            ->willReturn('/root/composer.json');

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                ['/config/dir/annotated-container.xml', false],
                ['/root/composer.json', true]
            ]);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/root/composer.json')
            ->willReturn(json_encode([
                'autoload' => [
                    'psr-4' => [
                        'Another\\Namespace\\' => ['src']
                    ]
                ],
            ], JSON_THROW_ON_ERROR));

        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="$version">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
    <vendor/>
  </scanDirectories>
  <definitionProviders/>
  <parameterStores>
    <parameterStore>MyParameterStoreClass</parameterStore>
  </parameterStores>
</annotatedContainer>

XML;

        $this->filesystem->expects($this->once())
            ->method('write')
            ->with('/config/dir/annotated-container.xml', $expected);

        $input = new StubInput(['parameter-store' => 'MyParameterStoreClass'], ['init']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
    }

    public function testMultipleParameterStoresRespected() : void {
        $this->directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/config/dir/annotated-container.xml');

        $this->directoryResolver->expects($this->once())
            ->method('rootPath')
            ->with('composer.json')
            ->willReturn('/root/composer.json');

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                ['/config/dir/annotated-container.xml', false],
                ['/root/composer.json', true]
            ]);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/root/composer.json')
            ->willReturn(json_encode([
                'autoload' => [
                    'psr-4' => [
                        'Another\\Namespace\\' => ['src']
                    ]
                ],
            ], JSON_THROW_ON_ERROR));

        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="$version">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
    <vendor/>
  </scanDirectories>
  <definitionProviders/>
  <parameterStores>
    <parameterStore>MyParameterStoreClassOne</parameterStore>
    <parameterStore>MyParameterStoreClassTwo</parameterStore>
  </parameterStores>
</annotatedContainer>

XML;

        $this->filesystem->expects($this->once())
            ->method('write')
            ->with('/config/dir/annotated-container.xml', $expected);

        $input = new StubInput(['parameter-store' => ['MyParameterStoreClassOne', 'MyParameterStoreClassTwo']], ['init']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
    }

    public function testDefinitionProviderBooleanThrowsException() : void {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('The option "definition-provider" MUST NOT be a flag-only option.');

        $input = new StubInput(['definition-provider' => true], ['init']);
        $this->subject->handle($input, $this->output);
    }

    public function testDefinitionProviderArrayThrowsException() : void {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('The option "definition-provider" MUST NOT be provided multiple times.');

        $input = new StubInput(['definition-provider' => ['a', 'b']], ['init']);
        $this->subject->handle($input, $this->output);
    }

    public function testParameterStoreBooleanThrowsException() : void {
        $this->expectException(InvalidOptionType::class);
        $this->expectExceptionMessage('The option "parameter-store" MUST NOT be a flag-only option.');

        $input = new StubInput(['parameter-store' => true], ['init']);
        $this->subject->handle($input, $this->output);
    }

    public function testSuccessfulRunHasCorrectOutput() : void {
        $this->directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/config/dir/annotated-container.xml');

        $this->directoryResolver->expects($this->once())
            ->method('rootPath')
            ->with('composer.json')
            ->willReturn('/root/composer.json');

        $this->filesystem->expects($this->exactly(2))
            ->method('exists')
            ->willReturnMap([
                ['/config/dir/annotated-container.xml', false],
                ['/root/composer.json', true]
            ]);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with('/root/composer.json')
            ->willReturn(json_encode([
                'autoload' => [
                    'psr-4' => [
                        'Another\\Namespace\\' => ['src']
                    ]
                ],
            ], JSON_THROW_ON_ERROR));

        $input = new StubInput([], ['init']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        $expected = <<<SHELL
\033[32mAnnotated Container initialized successfully!\033[0m

Be sure to review the configuration file created in "annotated-container.xml"!

SHELL;

        self::assertSame($expected, $this->stdout->getContentsAsString());
        self::assertEmpty($this->stderr->getContents());
    }
}
