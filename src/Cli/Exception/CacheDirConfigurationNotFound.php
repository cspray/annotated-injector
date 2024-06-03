<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Exception;

final class CacheDirConfigurationNotFound extends CliException {

    public static function fromBuildCommand() : self {
        return new self('Building a Container without a configured cache directory is not supported.');
    }

    public static function fromCacheCommand() : self {
        return new self('Clearing a cache without a cache directory configured is not supported.');
    }
}
