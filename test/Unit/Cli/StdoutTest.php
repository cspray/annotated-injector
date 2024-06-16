<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli;

use Cspray\AnnotatedContainer\Cli\Output\Stdout;
use Cspray\StreamBufferIntercept\Buffer;
use Cspray\StreamBufferIntercept\StreamFilter;
use PHPUnit\Framework\TestCase;

final class StdoutTest extends TestCase {

    private Buffer $buffer;

    protected function setUp() : void {
        StreamFilter::register();
        $this->buffer = StreamFilter::intercept(\STDOUT);
    }

    protected function tearDown() : void {
        $this->buffer->stopIntercepting();
    }

    public function testOutputsWithNewLine() : void {
        (new Stdout(STDOUT))->write('This is the output we expect to receive.');

        self::assertSame('This is the output we expect to receive.' . PHP_EOL, $this->buffer->output());
    }

    public function testOutputsWitoutNewLine() : void {
        (new Stdout(STDOUT))->write('Some output without a new line', false);

        self::assertSame('Some output without a new line', $this->buffer->output());
    }
}
