<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidAlias extends Exception {

    public static function fromAbstractNotService(string $service) : self {
        $message = sprintf('An AliasDefinition has an abstract type, %s, that is not a registered ServiceDefinition.', $service);
        return new self($message);
    }

    public static function fromConcreteNotService(string $service) : self {
        $message = sprintf('An AliasDefinition has a concrete type, %s, that is not a registered ServiceDefinition.', $service);
        return new self($message);
    }
}
