<?php

namespace Cspray\AnnotatedContainer\Unit\Cli;

use Cspray\AnnotatedContainer\Cli\Exception\OptionNotFound;
use Cspray\AnnotatedContainer\Cli\Input\InputParser;
use PHPUnit\Framework\TestCase;

class InputParserTest extends TestCase {

    public function testArgvWithOnlyScriptReturnsEmptyOptionsAndArguments() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php']);

        self::assertEmpty($input->options());
        self::assertEmpty($input->arguments());
    }

    public function testArgvWithScriptAndOnlyOneArgument() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', 'arg1']);

        self::assertEmpty($input->options());
        self::assertSame(['arg1'], $input->arguments());
    }

    public function testArgvWithScriptAndMultipleArguments() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', 'arg1', 'arg2']);

        self::assertEmpty($input->options());
        self::assertSame(['arg1', 'arg2'], $input->arguments());
    }

    public function testArgvWithScriptAndBoolOptions() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo', '--bar']);

        self::assertSame([
            'foo' => true,
            'bar' => true
        ], $input->options());
        self::assertEmpty($input->arguments());
    }

    public function testArgvWithScriptAndSingleOptionValues() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo=bar', '--baz=qux']);

        self::assertSame([
            'foo' => 'bar',
            'baz' => 'qux'
        ], $input->options());
        self::assertEmpty($input->arguments());
    }

    public function testArgvWithScriptAndArrayOptionValues() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo=bar', '--foo=baz', '--foo=qux']);

        self::assertSame([
            'foo' => ['bar', 'baz', 'qux']
        ], $input->options());
        self::assertEmpty($input->arguments());
    }

    public function testArgvWithScriptAndMixedBooleanAndStringValues() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo', '--foo=bar', '--foo=qux']);

        self::assertSame([
            'foo' => [true, 'bar', 'qux']
        ], $input->options());
        self::assertEmpty($input->arguments());
    }

    public function testArgvWithScriptSingleShortOptBoolean() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '-a']);

        self::assertSame([
            'a' => true
        ], $input->options());
        self::assertEmpty($input->arguments());
    }

    public function testArgvWithScriptSingleShortOptWithMultipleValues() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '-abc']);

        self::assertSame([
            'a' => true,
            'b' => true,
            'c' => true
        ], $input->options());
        self::assertEmpty($input->arguments());
    }

    public function testArgvWithScriptMultipleShortOpts() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '-a', '-b', '-c']);

        self::assertSame([
            'a' => true,
            'b' => true,
            'c' => true
        ], $input->options());
        self::assertEmpty($input->arguments());
    }

    public function testArgvWithScriptShortOptStringValue() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '-a=b', '-b=c', '-c=d']);

        self::assertSame([
            'a' => 'b',
            'b' => 'c',
            'c' => 'd'
        ], $input->options());
        self::assertEmpty($input->arguments());
    }

    public function testArgvWithNoOptionsGetOptionReturnsNull() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php']);

        self::assertNull($input->option('not-found'));
    }

    public function testArgvWithOptionGetsValue() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo']);

        self::assertTrue($input->option('foo'));
    }

    public function testArgvWithNoOptionRequireOptionThrowsException() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php']);

        self::expectException(OptionNotFound::class);
        self::expectExceptionMessage('The option "foo" was not provided.');

        $input->requireOption('foo');
    }

    public function testArgvWithOptionRequireValue() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo=bar']);

        self::assertSame('bar', $input->requireOption('foo'));
    }
}
