<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Exception;

final class ProfileNotString extends CliException {

    public static function fromNotString() : self {
        return new self('All provided profiles MUST be a string.');
    }
}
