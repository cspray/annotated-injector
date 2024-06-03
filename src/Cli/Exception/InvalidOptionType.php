<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Exception;

final class InvalidOptionType extends CliException {

    public static function fromBooleanOption(string $optionName) : self {
        return new self(sprintf(
            'The option "%s" MUST NOT be a flag-only option.',
            $optionName
        ));
    }

    public static function fromArrayOption(string $optionName) : self {
        return new self(sprintf(
            'The option "%s" MUST NOT be provided multiple times.',
            $optionName
        ));
    }
}
