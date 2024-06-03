<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Exception;

use Cspray\AnnotatedContainer\Exception\Exception;

final class ComposerConfigurationNotFound extends Exception {

    public static function fromMissingComposerJson() : self {
        $message = 'The file "composer.json" does not exist and is expected to be found.';
        return new self($message);
    }
}
