<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class UnableToReadFile extends Exception {

    public static function fromFailureToReadFromPath(string $path) : self {
        return new self(sprintf(
            'Failed reading contents from %s',
            $path
        ));
    }
}
