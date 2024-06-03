<?php

namespace Cspray\AnnotatedContainer\Unit\Cli;

use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use PHPUnit\Framework\TestCase;

class TerminalOutputTest extends TestCase {

    private InMemoryOutput $stdout;
    private InMemoryOutput $stderr;

    private TerminalOutput $subject;

    protected function setUp() : void {
        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->subject = new TerminalOutput($this->stdout, $this->stderr);
    }

    public function testWriteToStdout() : void {
        $this->subject->stdout->write('This is the message.');

        self::assertSame([
            'This is the message.' . PHP_EOL
        ], $this->stdout->getContents());
        self::assertEmpty($this->stderr->getContents());
    }

    public function testWriteToStderr() : void {
        $this->subject->stderr->write('Another message');

        self::assertEmpty($this->stdout->getContents());
        self::assertSame(['Another message' . PHP_EOL], $this->stderr->getContents());
    }

    public function testWriteFormatsBoldTag() : void {
        $this->subject->stdout->write('A message that includes <bold>text</bold>');

        self::assertSame([
            "A message that includes \033[1mtext\033[22m" . PHP_EOL
        ], $this->stdout->getContents());
    }

    public function testWriteFormatsItalicTag() : void {
        $this->subject->stdout->write('Some <em>italic text</em>');

        self::assertSame([
            "Some \033[3mitalic text\033[23m" . PHP_EOL
        ], $this->stdout->getContents());
    }

    public function testWriteFormatsBoldItalicTag() : void {
        $this->subject->stdout->write('Some <bold><em>bold AND italic</em></bold>');

        self::assertSame([
            "Some \033[1m\033[3mbold AND italic\033[23m\033[22m" . PHP_EOL
        ], $this->stdout->getContents());
    }

    public function testWriteFormatsUnderlineTag() : void {
        $this->subject->stdout->write('Message that <underline>underlines text</underline>');

        self::assertSame([
            "Message that \033[4munderlines text\033[24m" . PHP_EOL
        ], $this->stdout->getContents());
    }

    public function testWriteFormatsDimTag() : void {
        $this->subject->stdout->write('This should be <dim>faint</dim>');

        self::assertSame([
            "This should be \033[2mfaint\033[22m" . PHP_EOL
        ], $this->stdout->getContents());
    }

    public function testWriteFormatsStrikeTag() : void {
        $this->subject->stdout->write('Sometimes <del>striking through</del> is desired');

        self::assertSame([
            "Sometimes \033[9mstriking through\033[29m is desired" . PHP_EOL
        ], $this->stdout->getContents());
    }

    public static function colorProvider() : array {
        return [
            ['fg:black', '30'],
            ['bg:black', '40'],
            ['fg:red', '31'],
            ['bg:red', '41'],
            ['fg:green', '32'],
            ['bg:green', '42'],
            ['fg:yellow', '33'],
            ['bg:yellow', '43'],
            ['fg:blue', '34'],
            ['bg:blue', '44'],
            ['fg:magenta', '35'],
            ['bg:magenta', '45'],
            ['fg:cyan', '36'],
            ['bg:cyan', '46'],
            ['fg:white', '37'],
            ['bg:white', '47'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('colorProvider')]
    public function testWriteFormatsColorTag(string $color, string $code) : void {
        $this->subject->stdout->write(sprintf(
            'Now we need to have <%s>some stylized text</%1$s>',
            $color
        ));

        self::assertSame([
            sprintf("Now we need to have \033[%smsome stylized text\033[0m%s", $code, PHP_EOL)
        ], $this->stdout->getContents());
    }

    public function testWriteFormatsWithBgAndFgTags() : void {
        $this->subject->stdout->write('Need to have <bg:white><fg:black>both back and fore</fg:black></bg:white>');

        self::assertSame([
            "Need to have \033[47m\033[30mboth back and fore\033[0m\033[0m" . PHP_EOL
        ], $this->stdout->getContents());
    }
}
