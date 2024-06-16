<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Exception;

final class BlankOptName extends CliException {

    public static function fromBlankOpt() : self {
        return new self('A CLI opt MUST NOT be an empty string.');
    }
}
