<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Command\DisabledCommand;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubInput;
use PHPUnit\Framework\TestCase;

final class DisabledCommandTest extends TestCase {

    public function testNameIsSamePassedToConstructor() : void {
        $subject = new DisabledCommand(
            'my-command-name',
            ''
        );

        self::assertSame('my-command-name', $subject->name());
    }

    public function testSummaryExplainsToRunHelpCommandToEnable() : void {
        $subject = new DisabledCommand(
            'cmd',
            ''
        );

        self::assertSame(
            'Command is disabled. Run "help cmd" to learn how to enable it.',
            $subject->summary()
        );
    }

    public function testHelpOutputsHowToEnable() : void {
        $subject = new DisabledCommand(
            'cmd',
            'You should perform some task, which I am describing now.'
        );

        $expected = <<<TEXT
To enable "cmd":

You should perform some task, which I am describing now.

TEXT;

        self::assertSame($expected, $subject->help());
    }

    public function testHandleOutputsWarningMessageAndHelp() : void {
        $stdout = new InMemoryOutput();
        $stderr = new InMemoryOutput();
        $output = new TerminalOutput($stdout, $stderr);

        $subject = new DisabledCommand('cmd-name', 'How to enable');

        $exitCode = $subject->handle(new StubInput([], ['cmd-name']), $output);

        self::assertSame(1, $exitCode);
        self::assertEmpty($stdout->getContentsAsString());

        $expected = <<<TEXT
\033[41m\033[37mWarning! This command is disabled!\033[0m\033[0m

To enable "cmd-name":

How to enable


TEXT;

        self::assertSame($expected, $stderr->getContentsAsString());
    }

}
