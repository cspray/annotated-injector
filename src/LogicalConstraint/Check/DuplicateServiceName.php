<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;

final class DuplicateServiceName implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition, array $profiles) : LogicalConstraintViolationCollection {
        $containerDefinition = new ProfilesAwareContainerDefinition($containerDefinition, $profiles);
        $violations = new LogicalConstraintViolationCollection();

        /** @var array<string, list<class-string>> $namedServiceMap */
        $namedServiceMap = [];
        foreach ($containerDefinition->getServiceDefinitions() as $definition) {
            $name = $definition->getName();
            if ($name === null) {
                continue;
            }

            $namedServiceMap[$name] ??= [];
            $namedServiceMap[$name][] = $definition->getType()->getName();
        }

        foreach ($namedServiceMap as $name => $services) {
            if (count($services) > 1) {
                sort($services);
                $services = implode(
                    '- ',
                    array_map(static fn(string $type) => $type . PHP_EOL, $services)
                );
                $message = <<<TEXT
There are multiple services with the name "$name". The service types are:

- {$services}
TEXT;

                $violations->add(LogicalConstraintViolation::critical(trim($message)));
            }
        }

        return $violations;
    }
}
