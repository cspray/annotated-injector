<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Exception;

final class ConfigurationNotFound extends CliException {

    public static function fromMissingFile(string $file) : self {
        return new self(sprintf(
            'No configuration file found at "%s".',
            $file
        ));
    }
}
