<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Filesystem;

use Cspray\AnnotatedContainer\Exception\UnableToReadFile;
use Cspray\AnnotatedContainer\Exception\UnableToWriteFile;
use Cspray\AnnotatedContainer\Filesystem\PhpFunctionsFilesystem;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;

class PhpFunctionsFilesystemTest extends TestCase {

    private VirtualDirectory $vfs;
    private PhpFunctionsFilesystem $subject;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
        $this->subject = new PhpFunctionsFilesystem();
    }

    public function testIsFileReturnsFalseIfNoFileFound() : void {
        self::assertFalse(
            $this->subject->isFile('vfs://root/file.txt')
        );
    }

    public function testIsFileReturnsTrueIfFileIsFound() : void {
        VirtualFilesystem::newFile('file.txt')->at($this->vfs)->setContent('my file');

        self::assertTrue(
            $this->subject->isFile('vfs://root/file.txt')
        );
    }

    public function testIsFileReturnsFalseIfPathIsDirectory() : void {
        VirtualFilesystem::newDirectory('file')->at($this->vfs);

        self::assertFalse(
            $this->subject->isFile('vfs://root/file')
        );
    }

    public function testIsDirectoryReturnsFalseIfNothingAtPath() : void {
        self::assertFalse(
            $this->subject->isDirectory('vfs://root/dir')
        );
    }

    public function testIsDirectoryReturnsTrueIfDirectoryAtPath() : void {
        VirtualFilesystem::newDirectory('dir')->at($this->vfs);

        self::assertTrue(
            $this->subject->isDirectory('vfs://root/dir')
        );
    }

    public function testIsDirectoryReturnsFalseIfFileAtPath() : void {
        VirtualFilesystem::newFile('dir')->at($this->vfs);

        self::assertFalse(
            $this->subject->isDirectory('vfs://root/dir')
        );
    }

    public function testIsWritableReturnsFalseIfNothingAtPath() : void {
        self::assertFalse(
            $this->subject->isWritable('vfs://root/writable-dir')
        );
    }

    public function testIsWritableReturnsTrueIfWritableDirectoryAtPath() : void {
        VirtualFilesystem::newDirectory('write-dir', 0777)->at($this->vfs);

        self::assertTrue(
            $this->subject->isWritable('vfs://root/write-dir')
        );
    }

    public function testIsWritableReturnsTrueIfWritableFileAtPath() : void {
        VirtualFilesystem::newFile('write-file', 0777)->at($this->vfs);

        self::assertTrue(
            $this->subject->isWritable('vfs://root/write-file')
        );
    }

    public function testIsWritableReturnsFalseIfNonWritableDirectoryAtPath() : void {
        VirtualFilesystem::newDirectory('write-dir', 0400)->at($this->vfs);

        self::assertFalse(
            $this->subject->isWritable('vfs://root/write-dir')
        );
    }

    public function testIsWritableReturnsFalseIfNonWritableFileAtPath() : void {
        VirtualFilesystem::newFile('write-file', 0400)->at($this->vfs);

        self::assertFalse(
            $this->subject->isWritable('vfs://root/write-file')
        );
    }

    public function testWriteOnFileInDirectoryNotPresentThrowsException() : void {
        $this->expectException(UnableToWriteFile::class);
        $this->expectExceptionMessage('Failed writing contents to vfs://root/sub-dir/write-file');

        $this->subject->write('vfs://root/sub-dir/write-file', 'more type, less gripe');
    }

    public function testWriteOnFileInDirectoryPresentAddsCorrectContents() : void {
        self::assertFalse($this->vfs->hasChild('write-file'));

        $this->subject->write('vfs://root/write-file', 'that is the weirdest rule');

        self::assertSame(
            'that is the weirdest rule',
            $this->vfs->getChild('write-file')->getContent()
        );
    }

    public function testReadOnFileNotPresentThrowsException() : void {
        $this->expectException(UnableToReadFile::class);
        $this->expectExceptionMessage('Failed reading contents from vfs://root/read-file');

        $this->subject->read('vfs://root/read-file');
    }

    public function testReadOnFileIsPresentReturnsCorrectContent() : void {
        VirtualFilesystem::newFile('read-file')->at($this->vfs)->setContent('funny marmaduke on the fridge');

        self::assertSame(
            'funny marmaduke on the fridge',
            $this->subject->read('vfs://root/read-file')
        );
    }

    public function testRemoveFileDeletesFoundFile() : void {
        VirtualFilesystem::newFile('file')->at($this->vfs);

        $this->subject->remove('vfs://root/file');

        self::assertFalse($this->vfs->hasChild('file'));
    }

    public function testRemoveFileNotPresent() : void {
        $this->expectNotToPerformAssertions();

        $this->subject->remove('vfs://root/file');
    }

    public function testFilePresentAtPathExistsReturnsTrue() : void {
        VirtualFilesystem::newFile('file')->at($this->vfs);

        self::assertTrue($this->subject->exists('vfs://root/file'));
    }

    public function testFileNotPresentAtPathExistsReturnsFalse() : void {
        self::assertFalse($this->subject->exists('vfs://root/file'));
    }

    public function testDirPresentAtPathExistsReturnsTrue() : void {
        VirtualFilesystem::newDirectory('dir')->at($this->vfs);

        self::assertTrue($this->subject->exists('vfs://root/dir'));
    }
}
