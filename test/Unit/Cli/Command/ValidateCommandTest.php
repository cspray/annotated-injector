<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Cli\Command\ValidateCommand;
use Cspray\AnnotatedContainer\Cli\Exception\ConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceName;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServicePrepare;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceType;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\MultiplePrimaryForAbstractService;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServicePrepare;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubInput;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceType\DummyService;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\LogicalConstraintFixtures;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;

final class ValidateCommandTest extends TestCase {

    private VirtualDirectory $vfs;

    private ValidateCommand $subject;

    private InMemoryOutput $stdout;

    private InMemoryOutput $stderr;
    private TerminalOutput $output;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
        $this->subject = new ValidateCommand(
            new FixtureBootstrappingDirectoryResolver()
        );

        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->output = new TerminalOutput($this->stdout, $this->stderr);
    }

    public function testGetCommandName() : void {
        $actual = $this->subject->getName();

        self::assertSame('validate', $actual);
    }

    public function testGetCommandHelp() : void {
        $expected = <<<TEXT
NAME

    validate - Ensure container definition validates against all logical constraints.
    
SYNOPSIS

    <bold>validate</bold> [OPTION]...

DESCRIPTION

    <bold>validate</bold> will analyze your codebase, run a series of Logical Constraint 
    checks, and output any violations found.
    
    Violations are split into three different types:
    
    - Critical
        These errors are highly indicative of a problem that will result in an exception 
        at runtime. It is HIGHLY recommended that these violations are fixed immediately.
        
    - Warning
        These errors are likely indicative of a problem that will result in an exception 
        or error at runtime, but may not based on various conditions. It is recommended 
        that these violations are fixed as soon as possible.
        
    - Notice
        These errors will not cause an exception or error at runtime, but are likely 
        indicative of some problem or misunderstanding in your dependency injection 
        configuration. You should try to fix these violations when possible.

OPTIONS

    --config-file="file-path.xml"

        Set the name of the configuration file to be used. If not provided the
        default value will be "annotated-container.xml".
        
    --list-constraints
    
        Show which logical constraints will be used to validate your container 
        definition. Passing this options will only list constraints, validation 
        will NOT run with this option passed. 
        
    --profile
    
        Set the active profiles that are used when validating the Container. This 
        option can be provided multiple times to set more than 1 profile.

TEXT;

        self::assertSame($expected, $this->subject->getHelp());
    }

    public function testHandleWithNoConfigurationFilePresentHasTerminalError() : void {
        $input = new StubInput([], []);

        $this->expectException(ConfigurationNotFound::class);
        $this->expectExceptionMessage('No configuration file found at "vfs://root/annotated-container.xml".');

        $this->subject->handle($input, $this->output);
    }

    public function testHandleWithConfigurationFilePresentShowsNoLogicalConstraints() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        $expected = <<<TEXT
Annotated Container Validation

Configuration file: vfs://root/annotated-container.xml
Active Profiles: default

To view validations ran, execute "annotated-container validate --list-constraints"

\033[32mNo logical constraint violations were found!\033[0m

TEXT;

        $this->subject->handle(new StubInput([], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testHandleWithConfigurationFilePresentShowsLogicalConstraints() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>LogicalConstraints/DuplicateServiceName</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        $banner = str_repeat('*', 80);
        $barService = LogicalConstraintFixtures::duplicateServiceName()->getBarService()->getName();
        $fooService = LogicalConstraintFixtures::duplicateServiceName()->getFooService()->getName();
        $expected = <<<TEXT
Annotated Container Validation

Configuration file: vfs://root/annotated-container.xml
Active Profiles: default

To view validations ran, execute "annotated-container validate --list-constraints"

Violation #1 - \033[31mCritical\033[0m
$banner

There are multiple services with the name "foo". The service types are:

- $barService
- $fooService

\033[1m\033[31mERROR!\033[0m\033[22m Total violations found: \033[1m1\033[22m

TEXT;

        $this->subject->handle(new StubInput([], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testViolationWithWarningHasCorrectColorEncoded() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>LogicalConstraints/DuplicateServiceType</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        $banner = str_repeat('*', 80);
        $service = LogicalConstraintFixtures::duplicateServiceType()->fooService()->getName();
        $serviceAttr = Service::class;
        $dummyAttr = DummyService::class;
        $expected = <<<TEXT
Annotated Container Validation

Configuration file: vfs://root/annotated-container.xml
Active Profiles: default

To view validations ran, execute "annotated-container validate --list-constraints"

Violation #1 - \033[33mWarning\033[0m
$banner

The type "$service" has been defined multiple times!

- Attributed with $serviceAttr
- Attributed with $dummyAttr

This will result in undefined behavior, determined by the backing container, and 
should be avoided.

\033[1m\033[31mERROR!\033[0m\033[22m Total violations found: \033[1m1\033[22m

TEXT;

        $this->subject->handle(new StubInput([], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testWithListConstraintsOptionProvidedShowsCorrectOutput() : void {
        $dupeDelegate = DuplicateServiceDelegate::class;
        $dupeName = DuplicateServiceName::class;
        $dupePrepare = DuplicateServicePrepare::class;
        $dupeType = DuplicateServiceType::class;
        $multiplePrimary = MultiplePrimaryForAbstractService::class;
        $nonPublicDelegate = NonPublicServiceDelegate::class;
        $nonPublicPrepare = NonPublicServicePrepare::class;

        $expected = <<<TEXT
Annotated Container Validation

The following constraints will be checked when validate is ran:

- $dupeDelegate
- $dupeName
- $dupePrepare
- $dupeType
- $multiplePrimary
- $nonPublicDelegate
- $nonPublicPrepare

TEXT;

        $this->subject->handle(new StubInput(['list-constraints' => true], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testConfigFileOptionPassedRespected() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('custom-config.xml')
            ->withContent($config)
            ->at($this->vfs);

        $expected = <<<TEXT
Annotated Container Validation

Configuration file: vfs://root/custom-config.xml
Active Profiles: default

To view validations ran, execute "annotated-container validate --list-constraints"

\033[32mNo logical constraint violations were found!\033[0m

TEXT;

        $this->subject->handle(new StubInput(['config-file' => 'custom-config.xml'], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testProfilesRespectedInOutputAndContainerAnalysis() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        $expected = <<<TEXT
Annotated Container Validation

Configuration file: vfs://root/annotated-container.xml
Active Profiles: default, dev

To view validations ran, execute "annotated-container validate --list-constraints"

\033[32mNo logical constraint violations were found!\033[0m

TEXT;

        $this->subject->handle(new StubInput(['profile' => ['default', 'dev']], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testSingleProfileRespected() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        $expected = <<<TEXT
Annotated Container Validation

Configuration file: vfs://root/annotated-container.xml
Active Profiles: dev

To view validations ran, execute "annotated-container validate --list-constraints"

\033[32mNo logical constraint violations were found!\033[0m

TEXT;

        $this->subject->handle(new StubInput(['profile' => 'dev'], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }
}
