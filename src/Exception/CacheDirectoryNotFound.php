<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class CacheDirectoryNotFound extends Exception {

    public static function fromDirectoryNotFound(string $dir) : self {
        return new self(sprintf(
            'The cache directory configured, "%s", is not present.',
            $dir
        ));
    }
}
