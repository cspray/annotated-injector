<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Exception;

final class BlankArg extends CliException {

    public static function fromBlankArg() : self {
        return new self('A CLI argument MUST NOT be an empty string.');
    }
}
