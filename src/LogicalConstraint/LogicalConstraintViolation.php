<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

/**
 * Represents the details of a specific violation of a LogicalConstraint.
 */
final class LogicalConstraintViolation {

    private function __construct(
        public readonly string $message,
        public readonly LogicalConstraintViolationType $violationType
    ) {
    }

    public static function critical(string $message) : self {
        return new LogicalConstraintViolation($message, LogicalConstraintViolationType::Critical);
    }

    public static function warning(string $message) : self {
        return new LogicalConstraintViolation($message, LogicalConstraintViolationType::Warning);
    }
}
