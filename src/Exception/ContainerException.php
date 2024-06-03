<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Psr\Container\ContainerExceptionInterface;

final class ContainerException extends Exception implements ContainerExceptionInterface {

    public static function fromCaughtThrowable(\Throwable $throwable) : self {
        return new self($throwable->getMessage(), previous: $throwable);
    }
}
