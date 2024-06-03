<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class BackingContainerNotFound extends Exception {

    public static function fromMissingImplementation() : self {
        return new self('There is no backing Container library found. Please run "composer suggests" for supported containers.');
    }
}
