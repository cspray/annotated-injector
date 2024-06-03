<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidCache extends Exception {

    public static function fromUnwritableDirectory(string $dir) : self {
        $message = sprintf('The cache directory, %s, could not be written to. Please ensure it exists and is writeable.', $dir);
        return new self($message);
    }
}
