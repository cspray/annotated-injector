<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Filesystem;

interface Filesystem {

    public function exists(string $path) : bool;

    public function isDirectory(string $path) : bool;

    public function isFile(string $path) : bool;

    public function isWritable(string $path) : bool;

    public function write(string $path, string $contents) : void;

    public function read(string $path) : string;

    public function remove(string $path) : void;


}