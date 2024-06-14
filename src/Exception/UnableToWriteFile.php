<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

class UnableToWriteFile extends Exception {

    public static function fromFailureWritingToPath(string $path) : self {
        return new self(sprintf(
            'Failed writing contents to %s',
            $path
        ));
    }

}