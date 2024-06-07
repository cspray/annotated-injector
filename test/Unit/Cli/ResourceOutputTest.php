<?php

namespace Cspray\AnnotatedContainer\Unit\Cli;

use Cspray\AnnotatedContainer\Cli\Output\ResourceOutput;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;

class ResourceOutputTest extends TestCase {

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testWriteWithNewLineOutputsToFile() : void {
        VirtualFilesystem::newFile('some-file.txt')->at($this->vfs);

        $subject = new ResourceOutput(fopen('vfs://root/some-file.txt', 'w+'));
        $subject->write('Some message with a new line');

        $actual = file_get_contents('vfs://root/some-file.txt');
        $expected = 'Some message with a new line' . PHP_EOL;

        self::assertSame($expected, $actual);
    }

    public function testWriteWithoutNewLineOutputsFile() : void {
        VirtualFilesystem::newFile('some-file.txt')->at($this->vfs);

        $subject = new ResourceOutput(fopen('vfs://root/some-file.txt', 'w+'));
        $subject->write('Some message without a new line', false);

        $actual = file_get_contents('vfs://root/some-file.txt');
        $expected = 'Some message without a new line';

        self::assertSame($expected, $actual);
    }
}
