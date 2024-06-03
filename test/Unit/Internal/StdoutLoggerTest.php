<?php

namespace Cspray\AnnotatedContainer\Unit\Internal;

use Cspray\AnnotatedContainer\Internal\StdoutLogger;
use Cspray\AnnotatedContainer\Unit\Helper\StreamBuffer;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class StdoutLoggerTest extends TestCase {

    private $streamFilter;

    protected function setUp() : void {
        parent::setUp();
        if (!in_array('test.stream.buffer', stream_get_filters())) {
            self::assertTrue(stream_filter_register('test.stream.buffer', StreamBuffer::class));
        }
        $this->streamFilter = stream_filter_append(STDOUT, 'test.stream.buffer');
        self::assertIsResource($this->streamFilter);
        self::assertEmpty(StreamBuffer::getBuffer());
    }

    protected function tearDown() : void {
        parent::tearDown();
        StreamBuffer::clearBuffer();
        self::assertTrue(stream_filter_remove($this->streamFilter));
    }

    public function testWriteMessageToStdout() : void {
        $dateTime = new DateTimeImmutable('2022-01-01 13:00:00');
        $logger = new StdoutLogger(fn() => $dateTime);
        $logger->info(
            'This is a message that got passed.',
            ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux']
        );

        $time = $dateTime->format('Y-m-d\TH:i:s.uP');
        $expected = <<<FILE
[$time] annotated-container.INFO: This is a message that got passed. {"foo":"bar","bar":"baz","baz":"qux"}

FILE;

        self::assertSame($expected, StreamBuffer::getBuffer());
    }
}
