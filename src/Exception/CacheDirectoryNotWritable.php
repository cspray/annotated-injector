<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class CacheDirectoryNotWritable extends Exception {

    public static function fromDirectoryNotWritable(string $dir) : self {
        return new self(sprintf(
            'The cache directory configured, "%s", is not writable.',
            $dir
        ));
    }
}
