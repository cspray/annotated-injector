<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

/**
 * A class that ensures a ContainerDefinition adheres to the LogicalConstraints we have defined.
 */
final class LogicalConstraintValidator {

    /** @var LogicalConstraint[] */
    private array $logicalConstraints;

    public function __construct(
        LogicalConstraint...$logicalConstraints
    ) {
        $this->logicalConstraints = $logicalConstraints;
    }

    /**
     * Run all the LogicalConstraint implementations that this library defines and return a merged collection of any
     * violations that might exist in $containerDefinition.
     *
     * @param ContainerDefinition $containerDefinition
     * @param list<non-empty-string> $profiles
     * @return LogicalConstraintViolationCollection
     */
    public function validate(ContainerDefinition $containerDefinition, array $profiles) : LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();

        foreach ($this->logicalConstraints as $constraint) {
            $collection->addAll(
                $constraint->getConstraintViolations($containerDefinition, $profiles)
            );
        }

        return $collection;
    }
}
