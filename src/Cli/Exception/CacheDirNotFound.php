<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Exception;

final class CacheDirNotFound extends CliException {

    public static function fromMissingDirectory(string $dir) : self {
        return new self(sprintf(
            'The cache directory configured "%s" could not be found.',
            $dir
        ));
    }
}
