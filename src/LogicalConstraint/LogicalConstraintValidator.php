<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles;

/**
 * A class that ensures a ContainerDefinition adheres to the LogicalConstraints we have defined.
 */
final class LogicalConstraintValidator {

    /** @var LogicalConstraint[] */
    private readonly array $logicalConstraints;

    public function __construct(
        LogicalConstraint...$logicalConstraints
    ) {
        $this->logicalConstraints = $logicalConstraints;
    }

    /**
     * Run all the LogicalConstraint implementations that this library defines and return a merged collection of any
     * violations that might exist in $containerDefinition.
     */
    public function validate(ContainerDefinition $containerDefinition, Profiles $profiles) : LogicalConstraintViolationCollection {
        $collection = new LogicalConstraintViolationCollection();

        foreach ($this->logicalConstraints as $constraint) {
            $collection->addAll(
                $constraint->constraintViolations($containerDefinition, $profiles)
            );
        }

        return $collection;
    }
}
