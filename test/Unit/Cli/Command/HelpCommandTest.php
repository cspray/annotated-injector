<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Cli\Command\Command;
use Cspray\AnnotatedContainer\Cli\Command\CommandExecutor;
use Cspray\AnnotatedContainer\Cli\Command\DisabledCommand;
use Cspray\AnnotatedContainer\Cli\Command\HelpCommand;
use Cspray\AnnotatedContainer\Cli\Input\InputParser;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubCommand;
use Cspray\AnnotatedContainer\Unit\Helper\StubInput;
use PHPUnit\Framework\TestCase;

class HelpCommandTest extends TestCase {

    private CommandExecutor $commandExecutor;
    private HelpCommand $subject;

    protected function setUp() : void {
        $this->commandExecutor = new CommandExecutor();
        $this->subject = new HelpCommand($this->commandExecutor);
    }

    public function testHelpCommandName() : void {
        self::assertSame('help', $this->subject->name());
    }

    public function testHelpTextShowsAddedCommandsWithProperFormatting() : void {
        $a = $this->createMock(Command::class);
        $b = $this->createMock(Command::class);
        $c = $this->createMock(Command::class);

        $a->expects($this->exactly(2))->method('name')->willReturn('a-cmd');
        $a->expects($this->once())->method('summary')->willReturn('A Summary');

        $b->expects($this->exactly(2))->method('name')->willReturn('b-cmd');
        $b->expects($this->once())->method('summary')->willReturn('B Summary');

        $c->expects($this->exactly(2))->method('name')->willReturn('c-cmd');
        $c->expects($this->once())->method('summary')->willReturn('C Summary');

        $this->commandExecutor->addCommand($a);
        $this->commandExecutor->addCommand($b);
        $this->commandExecutor->addCommand($c);

        $version = AnnotatedContainerVersion::version();
        $expected = <<<TEXT
<bold>Annotated Container $version</bold>

This is a list of all available commands. For more information on a specific command please run "help <command-name>".
Commands listed in <fg:red>red</fg:red> are disabled and some action must be taken on your part to enable them.

<fg:green>a-cmd</fg:green>       A Summary
<fg:green>b-cmd</fg:green>       B Summary
<fg:green>c-cmd</fg:green>       C Summary

TEXT;

        self::assertSame($expected, $this->subject->help());
    }

    public function testDisabledCommandNamesAreShownInRed() : void {
        $version = AnnotatedContainerVersion::version();
        $expected = <<<TEXT
<bold>Annotated Container $version</bold>

This is a list of all available commands. For more information on a specific command please run "help <command-name>".
Commands listed in <fg:red>red</fg:red> are disabled and some action must be taken on your part to enable them.

<fg:red>bad-cmd</fg:red>         Command is disabled. Run "help bad-cmd" to learn how to enable it.

TEXT;

        $disabledCommand = new DisabledCommand('bad-cmd', 'Do some stuff');
        $this->commandExecutor->addCommand($disabledCommand);

        self::assertSame($expected, $this->subject->help());
    }

    public function testHelpCommandWithNoArgumentsPresentsDefaultHelpText() : void {
        $input = new StubInput([], ['help']);
        $terminalOutput = new TerminalOutput(
            $stdout = new InMemoryOutput(),
            $stderr = new InMemoryOutput()
        );

        $a = $this->createMock(Command::class);
        $a->expects($this->exactly(2))->method('name')->willReturn('cmd-name');
        $a->expects($this->once())->method('summary')->willReturn('My command summary');

        $this->commandExecutor->addCommand($a);

        $exitCode = $this->subject->handle($input, $terminalOutput);

        $version = AnnotatedContainerVersion::version();
        $expected = <<<SHELL
\033[1mAnnotated Container $version\033[22m

This is a list of all available commands. For more information on a specific command please run "help <command-name>".
Commands listed in \033[31mred\033[0m are disabled and some action must be taken on your part to enable them.

\033[32mcmd-name\033[0m    My command summary


SHELL;


        self::assertSame(0, $exitCode);
        self::assertSame($expected, $stdout->getContentsAsString());
        self::assertEmpty($stderr->getContents());
    }

    public function testHelpCommandWithArgumentCommandNotFound() : void {
        $input = new StubInput([], ['help', 'not-found']);
        $terminalOutput = new TerminalOutput(
            $stdout = new InMemoryOutput(),
            $stderr = new InMemoryOutput()
        );
        $exitCode = $this->subject->handle($input, $terminalOutput);
        $expected = <<<SHELL
\033[41m\033[37mCould not find command "not-found"!\033[0m\033[0m

SHELL;

        self::assertSame(1, $exitCode);
        self::assertEmpty($stdout->getContents());
        self::assertSame($expected, $stderr->getContentsAsString());
    }

    public function testHelpCommandWithArgumentAndCommandFound() : void {
        $input = (new InputParser())->parse(['script.php', 'help', 'foo']);

        $foo = $this->createMock(Command::class);
        $foo->expects($this->exactly(2))->method('name')->willReturn('foo');
        $foo->expects($this->once())->method('help')->willReturn('My help text');

        $this->commandExecutor->addCommand($foo);

        $terminalOutput = new TerminalOutput(
            $stdout = new InMemoryOutput(),
            $stderr = new InMemoryOutput()
        );
        $exitCode = $this->subject->handle($input, $terminalOutput);
        $expected = <<<SHELL
My help text

SHELL;

        self::assertSame(0, $exitCode);
        self::assertSame($expected, $stdout->getContentsAsString());
        self::assertEmpty($stderr->getContents());
    }
}
