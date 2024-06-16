<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Cli\AnnotatedContainerCliRunner;
use Cspray\AnnotatedContainer\Cli\Command\BuildCommand;
use Cspray\AnnotatedContainer\Cli\Command\CacheClearCommand;
use Cspray\AnnotatedContainer\Cli\Command\DisabledCommand;
use Cspray\AnnotatedContainer\Cli\Command\HelpCommand;
use Cspray\AnnotatedContainer\Cli\Command\InitCommand;
use Cspray\AnnotatedContainer\Cli\Command\ValidateCommand;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\StreamBufferIntercept\Buffer;
use Cspray\StreamBufferIntercept\StreamFilter;
use PHPUnit\Framework\TestCase;

final class AnnotatedContainerCliRunnerTest extends TestCase {

    private Buffer $stdoutBuffer;

    private Buffer $stderrBuffer;

    protected function setUp() : void {
        StreamFilter::register();
        $this->stdoutBuffer = StreamFilter::intercept(STDOUT);
        $this->stderrBuffer = StreamFilter::intercept(STDERR);
    }

    protected function tearDown() : void {
        $this->stdoutBuffer->stopIntercepting();
        $this->stderrBuffer->stopIntercepting();
    }

    public function testSetupWithNullConfigurationHasAppropriateActiveAndDisabledCommands() : void {
        $subject = AnnotatedContainerCliRunner::setup(null);

        $commands = $subject->commands();

        self::assertCount(5, $commands);

        self::assertInstanceOf(HelpCommand::class, $commands[0]);
        self::assertInstanceOf(InitCommand::class, $commands[1]);

        self::assertInstanceOf(DisabledCommand::class, $commands[2]);
        self::assertSame('build', $commands[2]->name());

        self::assertInstanceOf(DisabledCommand::class, $commands[3]);
        self::assertSame('cache-clear', $commands[3]->name());

        self::assertInstanceOf(DisabledCommand::class, $commands[4]);
        self::assertSame('validate', $commands[4]->name());
    }

    public function testSetupWithNullConfigurationHasCorrectBuildCommandHelp() : void {
        $subject = AnnotatedContainerCliRunner::setup(null);

        $commands = $subject->commands();

        self::assertCount(5, $commands);
        self::assertInstanceOf(DisabledCommand::class, $commands[2]);
        self::assertSame('build', $commands[2]->name());

        $boostrappingConfig = BootstrappingConfiguration::class;
        $expected = <<<TEXT
To enable "build":

A $boostrappingConfig object
with a cache() method that returns a non-null value. For more information,
read /docs/how-to/03-caching-container-definition.md.

TEXT;

        self::assertSame($expected, $commands[2]->help());
    }

    public function testSetupWithNullConfigurationHasCorrectCacheClearCommandHelp() : void {
        $subject = AnnotatedContainerCliRunner::setup(null);

        $commands = $subject->commands();

        self::assertCount(5, $commands);
        self::assertInstanceOf(DisabledCommand::class, $commands[3]);
        self::assertSame('cache-clear', $commands[3]->name());

        $boostrappingConfig = BootstrappingConfiguration::class;
        $expected = <<<TEXT
To enable "cache-clear":

A $boostrappingConfig object
with a cache() method that returns a non-null value. For more information,
read /docs/how-to/03-caching-container-definition.md.

TEXT;

        self::assertSame($expected, $commands[3]->help());
    }

    public function testSetupWithNullConfigurationHasHelpCommandAsDefault() : void {
        $subject = AnnotatedContainerCliRunner::setup(null);
        $subject->run(['script.php']);

        $version = AnnotatedContainerVersion::version();
        $expected = <<<TEXT
\033[1mAnnotated Container $version\033[22m

This is a list of all available commands. For more information on a specific command please run "help <command-name>".
Commands listed in \033[31mred\033[0m are disabled and some action must be taken on your part to enable them.

\033[31mbuild\033[0m            Command is disabled. Run "help build" to learn how to enable it.
\033[31mcache-clear\033[0m      Command is disabled. Run "help cache-clear" to learn how to enable it.
\033[32mhelp\033[0m             List available commands and show detailed info about individual commands.
\033[32minit\033[0m             Setup Annotated Container configuration using a set of common conventions.
\033[31mvalidate\033[0m         Command is disabled. Run "help validate" to learn how to enable it.


TEXT;

        self::assertEmpty($this->stderrBuffer->output());
        self::assertSame($expected, $this->stdoutBuffer->output());
    }

    public function testNullConfigurationHasAppropriateValidateCommandHelp() : void {
        $subject = AnnotatedContainerCliRunner::setup(null);

        $commands = $subject->commands();

        self::assertCount(5, $commands);
        self::assertInstanceOf(DisabledCommand::class, $commands[3]);
        self::assertSame('validate', $commands[4]->name());

        $bootstrappingConfig = BootstrappingConfiguration::class;
        $expected = <<<TEXT
To enable "validate":

A $bootstrappingConfig object
must be provided. This can be accomplished by running the "init" command.

TEXT;

        self::assertSame($expected, $commands[4]->help());
    }

    public function testConfigurationWithNullCacheHasAppropriateActiveAndDisabledCommands() : void {
        $configuration = $this->createMock(BootstrappingConfiguration::class);
        $configuration->expects($this->once())->method('cache')->willReturn(null);

        $subject = AnnotatedContainerCliRunner::setup($configuration);

        $commands = $subject->commands();

        self::assertCount(5, $commands);
        self::assertInstanceOf(HelpCommand::class, $commands[0]);
        self::assertInstanceOf(InitCommand::class, $commands[1]);

        self::assertInstanceOf(DisabledCommand::class, $commands[2]);
        self::assertSame('build', $commands[2]->name());

        self::assertInstanceOf(DisabledCommand::class, $commands[3]);
        self::assertSame('cache-clear', $commands[3]->name());

        self::assertInstanceOf(ValidateCommand::class, $commands[4]);
    }

    public function testConfigurationWithCacheHasAppropriateActiveAndDisabledCommands() : void {
        $configuration = $this->createMock(BootstrappingConfiguration::class);
        $configuration->expects($this->once())->method('cache')->willReturn(
            $this->createMock(ContainerDefinitionCache::class)
        );

        $subject = AnnotatedContainerCliRunner::setup($configuration);

        $commands = $subject->commands();

        self::assertCount(5, $commands);
        self::assertInstanceOf(HelpCommand::class, $commands[0]);
        self::assertInstanceOf(InitCommand::class, $commands[1]);
        self::assertInstanceOf(BuildCommand::class, $commands[2]);
        self::assertInstanceOf(CacheClearCommand::class, $commands[3]);
        self::assertInstanceOf(ValidateCommand::class, $commands[4]);
    }
}
