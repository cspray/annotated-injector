<?php

namespace Cspray\AnnotatedContainer\Exception;

final class ComposerAutoloadNotFound extends Exception {

    public static function fromMissingAutoload() : self {
        return new self(
            'Did not find any directories to scan based on composer autoload configuration. ' .
            'Please ensure there is a PSR-4 or PSR-0 autoload or autoload-dev set in your composer.json and try again.'
        );
    }
}
