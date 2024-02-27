<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidProfiles extends Exception {

    public static function fromEmptyProfilesList() : self {
        return new self('A non-empty list of non-empty strings MUST be provided for Profiles.');
    }

    public static function fromEmptyProfile() : self {
        return new self('All profiles MUST be non-empty strings.');
    }
}
