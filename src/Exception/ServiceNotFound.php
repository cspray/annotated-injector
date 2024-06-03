<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Psr\Container\NotFoundExceptionInterface;

final class ServiceNotFound extends Exception implements NotFoundExceptionInterface {

    public static function fromServiceNotInContainer(string $id) : self {
        $message = sprintf(
            'The service "%s" could not be found in this container.',
            $id
        );
        return new self($message);
    }
}
