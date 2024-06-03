<?php

namespace Cspray\AnnotatedContainer\Unit\Internal;

use Cspray\AnnotatedContainer\Exception\InvalidLogFile;
use Cspray\AnnotatedContainer\Exception\InvalidLogFileException;
use Cspray\AnnotatedContainer\Internal\FileLogger;
use DateTime;
use DateTimeImmutable;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use PHPUnit\Framework\TestCase;

class FileLoggerTest extends TestCase {

    protected function setUp() : void {
        VirtualFilesystem::setup();
    }

    public function testFileDoesNotExistTouchesPath() : void {
        self::assertFileDoesNotExist('vfs://root/annotated-container.log');

        new FileLogger(
            fn() => new DateTimeImmutable(),
            'vfs://root/annotated-container.log'
        );

        self::assertFileExists('vfs://root/annotated-container.log');
    }

    public function testFileNotTouchableThrowsException() : void {
        self::expectException(InvalidLogFile::class);
        self::expectExceptionMessage(
            'Unable to write to log file "vfs://root/nested/path/annotated-container.log".'
        );

        new FileLogger(
            fn() => new DateTimeImmutable(),
            'vfs://root/nested/path/annotated-container.log'
        );
    }

    public function testFileWritesContent() : void {
        $dateTime = new DateTimeImmutable('2022-01-01 13:00:00');
        $logger = new FileLogger(fn() => $dateTime, 'vfs://root/annotated-container.log');

        $logger->info(
            'This is a message that got passed.',
            ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux']
        );

        $time = $dateTime->format('Y-m-d\TH:i:s.uP');
        $expected = <<<FILE
[$time] annotated-container.INFO: This is a message that got passed. {"foo":"bar","bar":"baz","baz":"qux"}

FILE;

        self::assertStringEqualsFile('vfs://root/annotated-container.log', $expected);
    }

    public function testFileAppendsContent() : void {
        $dateTime = new DateTimeImmutable('2022-01-01 13:00:00');
        $logger = new FileLogger(fn() => $dateTime, 'vfs://root/annotated-container.log');

        $logger->info(
            'This is a message that got passed.',
            ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux']
        );
        $logger->info('Second message.');

        $time = $dateTime->format('Y-m-d\TH:i:s.uP');
        $expected = <<<FILE
[$time] annotated-container.INFO: This is a message that got passed. {"foo":"bar","bar":"baz","baz":"qux"}
[$time] annotated-container.INFO: Second message. {}

FILE;

        self::assertStringEqualsFile('vfs://root/annotated-container.log', $expected);
    }
}
