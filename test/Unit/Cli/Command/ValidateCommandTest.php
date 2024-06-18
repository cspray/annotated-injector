<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Cli\Command\ValidateCommand;
use Cspray\AnnotatedContainer\Cli\Exception\ProfileNotString;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
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
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceType\DummyService;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\LogicalConstraintFixtures;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ValidateCommandTest extends TestCase {


    private MockObject&BootstrappingConfiguration $bootstrappingConfiguration;

    private ValidateCommand $subject;

    private InMemoryOutput $stdout;

    private InMemoryOutput $stderr;
    private TerminalOutput $output;

    protected function setUp() : void {
        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->output = new TerminalOutput($this->stdout, $this->stderr);

        $this->bootstrappingConfiguration = $this->createMock(BootstrappingConfiguration::class);
        $this->subject = new ValidateCommand(
            $this->bootstrappingConfiguration,
            new FixtureBootstrappingDirectoryResolver(),
        );
    }

    public function testGetCommandName() : void {
        $actual = $this->subject->name();

        self::assertSame('validate', $actual);
    }

    public function testGetCommandHelp() : void {
        $expected = <<<TEXT
NAME

    validate - Ensure your ContainerDefinition validates against all logical constraints
    
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

        self::assertSame($expected, $this->subject->help());
    }

    public function testHandleWithConfigurationFilePresentShowsNoLogicalConstraints() : void {
        $configClass = $this->bootstrappingConfiguration::class;
        $expected = <<<TEXT
Annotated Container Validation

Configuration: $configClass
Active Profiles: default

To view validations ran, execute "annotated-container validate --list-constraints"

\033[32mNo logical constraint violations were found!\033[0m

TEXT;

        $this->bootstrappingConfiguration->expects($this->once())
            ->method('scanDirectories')
            ->willReturn(['SingleConcreteService']);

        $this->subject->handle(new StubInput([], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testHandleWithConfigurationFilePresentShowsLogicalConstraints() : void {
        $banner = str_repeat('*', 80);
        $barService = LogicalConstraintFixtures::duplicateServiceName()->getBarService()->name();
        $fooService = LogicalConstraintFixtures::duplicateServiceName()->getFooService()->name();
        $configClass = $this->bootstrappingConfiguration::class;
        $expected = <<<TEXT
Annotated Container Validation

Configuration: $configClass
Active Profiles: default

To view validations ran, execute "annotated-container validate --list-constraints"

Violation #1 - \033[31mCritical\033[0m
$banner

There are multiple services with the name "foo". The service types are:

- $barService
- $fooService

\033[1m\033[31mERROR!\033[0m\033[22m Total violations found: \033[1m1\033[22m

TEXT;

        $this->bootstrappingConfiguration->expects($this->once())
            ->method('scanDirectories')
            ->willReturn(['LogicalConstraints/DuplicateServiceName']);
        $this->subject->handle(new StubInput([], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testViolationWithWarningHasCorrectColorEncoded() : void {
        $banner = str_repeat('*', 80);
        $service = LogicalConstraintFixtures::duplicateServiceType()->fooService()->name();
        $serviceAttr = Service::class;
        $dummyAttr = DummyService::class;
        $configClass = $this->bootstrappingConfiguration::class;
        $expected = <<<TEXT
Annotated Container Validation

Configuration: $configClass
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

        $this->bootstrappingConfiguration->expects($this->once())
            ->method('scanDirectories')
            ->willReturn(['LogicalConstraints/DuplicateServiceType']);
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

        $this->bootstrappingConfiguration->expects($this->never())
            ->method('scanDirectories');
        $this->subject->handle(new StubInput(['list-constraints' => true], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testProfilesRespectedInOutputAndContainerAnalysis() : void {
        $configClass = $this->bootstrappingConfiguration::class;
        $expected = <<<TEXT
Annotated Container Validation

Configuration: $configClass
Active Profiles: default, dev

To view validations ran, execute "annotated-container validate --list-constraints"

\033[32mNo logical constraint violations were found!\033[0m

TEXT;

        $this->bootstrappingConfiguration->expects($this->once())
            ->method('scanDirectories')
            ->willReturn(['SingleConcreteService']);
        $this->subject->handle(new StubInput(['profiles' => ['default', 'dev']], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testSingleProfileRespected() : void {
        $configClass = $this->bootstrappingConfiguration::class;
        $expected = <<<TEXT
Annotated Container Validation

Configuration: $configClass
Active Profiles: dev

To view validations ran, execute "annotated-container validate --list-constraints"

\033[32mNo logical constraint violations were found!\033[0m

TEXT;

        $this->bootstrappingConfiguration->expects($this->once())
            ->method('scanDirectories')
            ->willReturn(['SingleConcreteService']);
        $this->subject->handle(new StubInput(['profiles' => 'dev'], []), $this->output);

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testProfilesMixedBoolAndStringThrowsException() : void {
        $this->expectException(ProfileNotString::class);
        $this->expectExceptionMessage('All provided profiles MUST be a string.');

        $this->subject->handle(new StubInput(['profiles' => ['dev', true]], []), $this->output);
    }

    public function testProfilesOnlyBoolAndStringThrowsException() : void {
        $this->expectException(ProfileNotString::class);
        $this->expectExceptionMessage('All provided profiles MUST be a string.');

        $this->subject->handle(new StubInput(['profiles' => true], []), $this->output);
    }

    public function testGetSummaryHasExpectedType() : void {
        self::assertSame(
            'Ensure your ContainerDefinition validates against all logical constraints',
            $this->subject->summary()
        );
    }
}
