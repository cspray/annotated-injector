<?php

namespace Cspray\AnnotatedContainer\Unit\Cli;

use Cspray\AnnotatedContainer\Cli\Exception\OptionNotFound;
use Cspray\AnnotatedContainer\Cli\InputParser;
use PHPUnit\Framework\TestCase;

class InputParserTest extends TestCase {

    public function testArgvWithOnlyScriptReturnsEmptyOptionsAndArguments() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php']);

        self::assertEmpty($input->getOptions());
        self::assertEmpty($input->getArguments());
    }

    public function testArgvWithScriptAndOnlyOneArgument() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', 'arg1']);

        self::assertEmpty($input->getOptions());
        self::assertSame(['arg1'], $input->getArguments());
    }

    public function testArgvWithScriptAndMultipleArguments() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', 'arg1', 'arg2']);

        self::assertEmpty($input->getOptions());
        self::assertSame(['arg1', 'arg2'], $input->getArguments());
    }

    public function testArgvWithScriptAndBoolOptions() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo', '--bar']);

        self::assertSame([
            'foo' => true,
            'bar' => true
        ], $input->getOptions());
        self::assertEmpty($input->getArguments());
    }

    public function testArgvWithScriptAndSingleOptionValues() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo=bar', '--baz=qux']);

        self::assertSame([
            'foo' => 'bar',
            'baz' => 'qux'
        ], $input->getOptions());
        self::assertEmpty($input->getArguments());
    }

    public function testArgvWithScriptAndArrayOptionValues() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo=bar', '--foo=baz', '--foo=qux']);

        self::assertSame([
            'foo' => ['bar', 'baz', 'qux']
        ], $input->getOptions());
        self::assertEmpty($input->getArguments());
    }

    public function testArgvWithScriptAndMixedBooleanAndStringValues() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo', '--foo=bar', '--foo=qux']);

        self::assertSame([
            'foo' => [true, 'bar', 'qux']
        ], $input->getOptions());
        self::assertEmpty($input->getArguments());
    }

    public function testArgvWithScriptSingleShortOptBoolean() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '-a']);

        self::assertSame([
            'a' => true
        ], $input->getOptions());
        self::assertEmpty($input->getArguments());
    }

    public function testArgvWithScriptSingleShortOptWithMultipleValues() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '-abc']);

        self::assertSame([
            'a' => true,
            'b' => true,
            'c' => true
        ], $input->getOptions());
        self::assertEmpty($input->getArguments());
    }

    public function testArgvWithScriptMultipleShortOpts() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '-a', '-b', '-c']);

        self::assertSame([
            'a' => true,
            'b' => true,
            'c' => true
        ], $input->getOptions());
        self::assertEmpty($input->getArguments());
    }

    public function testArgvWithScriptShortOptStringValue() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '-a=b', '-b=c', '-c=d']);

        self::assertSame([
            'a' => 'b',
            'b' => 'c',
            'c' => 'd'
        ], $input->getOptions());
        self::assertEmpty($input->getArguments());
    }

    public function testArgvWithNoOptionsGetOptionReturnsNull() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php']);

        self::assertNull($input->getOption('not-found'));
    }

    public function testArgvWithOptionGetsValue() : void {
        $subject = new InputParser();
        $input = $subject->parse(['script.php', '--foo']);

        self::assertTrue($input->getOption('foo'));
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
