<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class UnsupportedOperation extends Exception {

    public static function fromMethodNotSupported(string $method) : self {
        return new self(sprintf('%s is not supported.', $method));
    }
}
