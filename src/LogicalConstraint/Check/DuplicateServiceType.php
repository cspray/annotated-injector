<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;

final class DuplicateServiceType implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition, array $profiles) : LogicalConstraintViolationCollection {
        $violations = new LogicalConstraintViolationCollection();

        /**
         * @var array<non-empty-string, list<ServiceAttribute|null>> $serviceTypeMap
         */
        $serviceTypeMap = [];

        foreach ($containerDefinition->getServiceDefinitions() as $definition) {
            $type = $definition->getType()->getName();
            $serviceTypeMap[$type] ??= [];
            $serviceTypeMap[$type][] = $definition->getAttribute();
        }

        foreach ($serviceTypeMap as $type => $attributes) {
            if (count($attributes) > 1) {
                $attributeTypes = trim(implode('- ', array_map(
                    static fn(?ServiceAttribute $attribute) => ($attribute === null ? 'Call to service() in DefinitionProvider' : 'Attributed with ' . $attribute::class) . PHP_EOL,
                    $attributes
                )));
                $message = <<<TEXT
The type "$type" has been defined multiple times!

- $attributeTypes

This will result in undefined behavior, determined by the backing container, and 
should be avoided.

TEXT;

                $violations->add(
                    LogicalConstraintViolation::warning(
                        $message,
                    )
                );
            }
        }

        return $violations;
    }
}
