<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli;

use Cspray\AnnotatedContainer\Cli\Output\Stderr;
use Cspray\AnnotatedContainer\Unit\Helper\StreamBuffer;
use Cspray\StreamBufferIntercept\Buffer;
use Cspray\StreamBufferIntercept\StreamFilter;
use PHPUnit\Framework\TestCase;

final class StderrTest extends TestCase {

    private Buffer $buffer;

    protected function setUp() : void {
        StreamFilter::register();
        $this->buffer = StreamFilter::intercept(\STDERR);
    }

    protected function tearDown() : void {
        $this->buffer->stopIntercepting();
    }

    public function testOutputsWithNewLine() : void {
        (new Stderr(STDERR))->write('This is the output we expect to receive.');

        self::assertSame('This is the output we expect to receive.' . PHP_EOL, $this->buffer->output());
    }

    public function testOutputsWitoutNewLine() : void {
        (new Stderr(STDERR))->write('Some output without a new line', false);

        self::assertSame('Some output without a new line', $this->buffer->output());
    }
}
