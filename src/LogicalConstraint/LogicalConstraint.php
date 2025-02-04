<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

/**
 * Represents a check on a ContainerDefinition to ensure that the configured Container would behave according to the
 * specifics of the implemented constraint.
 */
interface LogicalConstraint {

    /**
     * Return 0 LogicalConstraintViolations in the returned collection if there are no errors or populate the returned
     * collection with each violation that the implemented constraint checks.
     *
     * @param ContainerDefinition $containerDefinition
     * @param list<non-empty-string> $profiles
     * @return LogicalConstraintViolationCollection
     */
    public function getConstraintViolations(
        ContainerDefinition $containerDefinition,
        array $profiles
    ) : LogicalConstraintViolationCollection;
}
