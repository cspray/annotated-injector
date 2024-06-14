<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Filesystem;

use Cspray\AnnotatedContainer\Exception\UnableToReadFile;
use Cspray\AnnotatedContainer\Exception\UnableToWriteFile;

final class PhpFunctionsFilesystem implements Filesystem {

    public function exists(string $path) : bool {
        return file_exists($path);
    }

    public function isDirectory(string $path) : bool {
        return is_dir($path);
    }

    public function isFile(string $path) : bool {
        return is_file($path);
    }

    public function isWritable(string $path) : bool {
        return is_writable($path);
    }

    public function write(string $path, string $contents) : void {
        if (! @file_put_contents($path, $contents)) {
            throw UnableToWriteFile::fromFailureWritingToPath($path);
        }
    }

    public function read(string $path) : string {
        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw UnableToReadFile::fromFailureToReadFromPath($path);
        }

        return $contents;
    }

    public function remove(string $path) : void {
        if ($this->isFile($path)) {
            unlink($path);
        }
    }

}