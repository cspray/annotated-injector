<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidAnnotatedTarget extends Exception {

    public static function fromAttributeInstanceNotKnownType() : self {
        return new self(
            'Received an AnnotatedTarget with an attribute instance of an unknown type. This is indicative of ' .
            'an internal error and should be reported to https://github.com/cspray/annotated-container.'
        );
    }
}
