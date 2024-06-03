<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class AutowireParameterNotFound extends Exception {

    public static function fromIndexNotFound(int $index) : self {
        $message = sprintf('There is no parameter found at index %s', $index);
        return new self($message);
    }
}
