<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class UnknownReflectionType extends Exception {

    public static function fromReflectionTypeInvalid() : self {
        return new self('An unknown ReflectionType encountered, only ReflectionNamedType, ReflectionUnionType, and ReflectionIntersectionType are supported.');
    }
}
