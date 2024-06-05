<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Cli\Command\HelpCommand;
use Cspray\AnnotatedContainer\Cli\CommandExecutor;
use Cspray\AnnotatedContainer\Cli\InputParser;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubCommand;
use PHPUnit\Framework\TestCase;

class HelpCommandTest extends TestCase {

    private CommandExecutor $commandExecutor;
    private HelpCommand $subject;

    protected function setUp() : void {
        $this->commandExecutor = new CommandExecutor();
        $this->subject = new HelpCommand($this->commandExecutor);
    }

    private function getExpectedHelp() : string {
        $version = AnnotatedContainerVersion::version();
        return <<<SHELL
<bold>Annotated Container $version</bold>

Available Commands:

init

\tSetup your app to scan Composer directories, cache your ContainerDefinition, 
\tand generate an appropriate configuration file.

build

\tBuild your ContainerDefinition from the configuration file and cache it. 
\tBuilding a ContainerDefinition without configuring cache support will result 
\tin an error.

cache-clear

\tDestroy the cache to allow rebuilding the Container.
    
For more help:

help <command-name>
SHELL;
    }

    public function testHelpCommandName() : void {
        self::assertSame('help', $this->subject->name());
    }

    public function testHelpCommandGetHelp() : void {
        self::assertSame($this->getExpectedHelp(), $this->subject->help());
    }

    public function testHelpCommandHandleNoArguments() : void {
        $input = (new InputParser())->parse(['script.php', 'help']);
        $terminalOutput = new TerminalOutput(
            $stdout = new InMemoryOutput(),
            $stderr = new InMemoryOutput()
        );
        $exitCode = $this->subject->handle($input, $terminalOutput);
        $version = AnnotatedContainerVersion::version();
        $expected = <<<SHELL
\033[1mAnnotated Container $version\033[22m

Available Commands:

init

\tSetup your app to scan Composer directories, cache your ContainerDefinition, 
\tand generate an appropriate configuration file.

build

\tBuild your ContainerDefinition from the configuration file and cache it. 
\tBuilding a ContainerDefinition without configuring cache support will result 
\tin an error.

cache-clear

\tDestroy the cache to allow rebuilding the Container.
    
For more help:

help <command-name>

SHELL;


        self::assertSame(0, $exitCode);
        self::assertSame($expected, $stdout->getContentsAsString());
        self::assertEmpty($stderr->getContents());
    }

    public function testHelpCommandWithArgumentCommandNotFound() : void {
        $input = (new InputParser())->parse(['script.php', 'help', 'not-found']);
        $terminalOutput = new TerminalOutput(
            $stdout = new InMemoryOutput(),
            $stderr = new InMemoryOutput()
        );
        $exitCode = $this->subject->handle($input, $terminalOutput);
        $expected = <<<SHELL
\033[31mCould not find command "not-found"!\033[0m

SHELL;

        self::assertSame(1, $exitCode);
        self::assertEmpty($stdout->getContents());
        self::assertSame($expected, $stderr->getContentsAsString());
    }

    public function testHelpCommandWithTooManyArguments() : void {
        $input = (new InputParser())->parse(['script.php', 'help', 'foo', 'bar']);
        $terminalOutput = new TerminalOutput(
            $stdout = new InMemoryOutput(),
            $stderr = new InMemoryOutput()
        );
        $exitCode = $this->subject->handle($input, $terminalOutput);
        $version = AnnotatedContainerVersion::version();
        $expected = <<<SHELL
\033[41m\033[37m!! Warning !!\033[0m\033[0m - Expecting 1 arg, showing default help

\033[1mAnnotated Container $version\033[22m

Available Commands:

init

\tSetup your app to scan Composer directories, cache your ContainerDefinition, 
\tand generate an appropriate configuration file.

build

\tBuild your ContainerDefinition from the configuration file and cache it. 
\tBuilding a ContainerDefinition without configuring cache support will result 
\tin an error.

cache-clear

\tDestroy the cache to allow rebuilding the Container.
    
For more help:

help <command-name>

SHELL;

        self::assertSame(0, $exitCode);
        self::assertSame($expected, $stdout->getContentsAsString());
        self::assertEmpty($stderr->getContents());
    }

    public function testHelpCommandWithArgumentAndCommandFound() : void {
        $input = (new InputParser())->parse(['script.php', 'help', 'foo']);
        $stubCommand = new StubCommand('foo', function(): never { throw new \Exception('Should not run');
        });
        $this->commandExecutor->addCommand($stubCommand);

        $terminalOutput = new TerminalOutput(
            $stdout = new InMemoryOutput(),
            $stderr = new InMemoryOutput()
        );
        $exitCode = $this->subject->handle($input, $terminalOutput);
        $expected = <<<SHELL
Stub command help text

SHELL;

        self::assertSame(0, $exitCode);
        self::assertSame($expected, $stdout->getContentsAsString());
        self::assertEmpty($stderr->getContents());
    }
}
