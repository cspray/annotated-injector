<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli;

use Cspray\AnnotatedContainer\Cli\CommandExecutor;
use Cspray\AnnotatedContainer\Cli\Input;
use Cspray\AnnotatedContainer\Cli\InputParser;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubCommand;
use PHPUnit\Framework\TestCase;

class CommandExecutorTest extends TestCase {

    private InputParser $inputParser;

    private InMemoryOutput $stdout;
    private InMemoryOutput $stderr;

    private TerminalOutput $terminalOutput;

    private CommandExecutor $subject;

    protected function setUp() : void {
        parent::setUp();
        $this->inputParser = new InputParser();
        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->terminalOutput = new TerminalOutput($this->stdout, $this->stderr);
        $this->subject = new CommandExecutor();
    }

    public function testCommandNotFoundHasExitCode() : void {
        $input = $this->inputParser->parse(['script.php']);     // no command present in the input
        $code = $this->subject->execute($input, $this->terminalOutput);

        self::assertSame(1, $code);
    }

    public function testCommandNotFoundHasCorrectOutput() : void {
        $input = $this->inputParser->parse(['script.php']);     // no command present in the input
        $this->subject->execute($input, $this->terminalOutput);

        self::assertEmpty($this->stdout->getContents());

        $expected = <<<SHELL
\033[31mUnable to find command to execute!\033[0m

Available Commands:

\tNo Commands Found

SHELL;

        self::assertSame($expected, $this->stderr->getContentsAsString());
    }

    public function testDefaultCommandExecutedIfPresent() : void {
        $input = $this->inputParser->parse(['script.php']);     // no command present in the input
        $actualInput = null;
        $command = new StubCommand('default', function(Input $input) use(&$actualInput) {
            $actualInput = $input;
        });
        $this->subject->setDefaultCommand($command);
        $this->subject->execute($input, $this->terminalOutput);

        self::assertEmpty($this->stdout->getContents());
        self::assertEmpty($this->stderr->getContents());
        self::assertNotNull($actualInput);
    }

    public function testShowAvailableCommandsIfCommandAddedAndNoDefault() : void {
        $input = $this->inputParser->parse(['script.php']);     // no command present in the input
        $fooInput = null;
        $fooCommand = new StubCommand('foo', function(Input $input) use(&$fooInput) {
            $fooInput = $input;
        });
        $barInput = null;
        $barCommand = new StubCommand('bar', function(Input $input) use(&$barInput) {
            $barInput = $input;
        });
        $this->subject->addCommand($fooCommand);
        $this->subject->addCommand($barCommand);
        $this->subject->execute($input, $this->terminalOutput);

        self::assertEmpty($this->stdout->getContents());

        $expected = <<<SHELL
\033[31mUnable to find command to execute!\033[0m

Available Commands:

\tbar
\tfoo

SHELL;

        self::assertSame($expected, $this->stderr->getContentsAsString());
        self::assertNull($fooInput);
        self::assertNull($barInput);
    }

    public function testExecutedAddedCommandIfProvidedInArgs() : void {
        $input = $this->inputParser->parse(['script.php', 'foobar']);
        $actualInput = null;
        $command = new StubCommand('foobar', function(Input $input) use(&$actualInput) {
            $actualInput = $input;
        });
        $this->subject->addCommand($command);
        $this->subject->execute($input, $this->terminalOutput);

        self::assertEmpty($this->stdout->getContents());
        self::assertEmpty($this->stdout->getContents());
        self::assertNotNull($actualInput);
    }

    public function testExecutedArgumentProvidedWithNoMatchingCommand() : void {
        $input = $this->inputParser->parse(['script.php', 'foobar']);
        $this->subject->execute($input, $this->terminalOutput);

        self::assertEmpty($this->stdout->getContents());

        $expected = <<<SHELL
\033[31mUnable to find command "foobar"!\033[0m

Available Commands:

\tNo Commands Found

SHELL;

        self::assertSame($expected, $this->stderr->getContentsAsString());
    }

    public function testExitCodeReturnedFromCommand() : void {
        $input = $this->inputParser->parse(['script.php', 'baz']);
        $actualInput = null;
        $command = new StubCommand('baz', function(Input $input) use(&$actualInput) {
            $actualInput = $input;
            return 0;
        });
        $this->subject->addCommand($command);
        $exitCode = $this->subject->execute($input, $this->terminalOutput);

        self::assertEmpty($this->stdout->getContents());
        self::assertEmpty($this->stdout->getContents());
        self::assertNotNull($actualInput);
        self::assertSame(0, $exitCode);
    }

    public function testCommandThrowsExceptionHasCorrectOutput() : void {
        $input = $this->inputParser->parse(['script.php', 'baz']);
        $command = new StubCommand('baz', function() {
            throw new \Exception('An exception was thrown.');
        });
        $this->subject->addCommand($command);
        $exitCode = $this->subject->execute($input, $this->terminalOutput);

        self::assertSame(1, $exitCode);
        self::assertEmpty($this->stdout->getContents());
        $file = __FILE__;
        $expected = <<<SHELL
Unhandled exception executing "baz"!

Type: Exception
Message: An exception was thrown.
Location: {$file}L#154
Stack Trace:

%a
SHELL;
        self::assertStringMatchesFormat($expected, $this->stderr->getContentsAsString());
    }

    public function testCommandThrowsExceptionHasExitCodeFromException() : void {
        $input = $this->inputParser->parse(['something.php', 'qux']);
        $command = new StubCommand('qux', function() {
            throw new \Exception('An exception was thrown.', 255);
        });
        $this->subject->addCommand($command);
        $exitCode = $this->subject->execute($input, $this->terminalOutput);

        self::assertSame(255, $exitCode);
    }
}
