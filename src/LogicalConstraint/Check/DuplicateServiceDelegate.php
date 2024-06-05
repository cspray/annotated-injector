<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;
use Cspray\AnnotatedContainer\Profiles;

final class DuplicateServiceDelegate implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition, Profiles $profiles) : LogicalConstraintViolationCollection {
        $violations = new LogicalConstraintViolationCollection();

        /**
         * @var array<non-empty-string, list<string>> $delegateMap
         */
        $delegateMap = [];

        foreach ($containerDefinition->serviceDelegateDefinitions() as $definition) {
            $service = $definition->serviceType()->getName();
            $method = sprintf('%s::%s', $definition->delegateType()->getName(), $definition->delegateMethod());
            $delegateMap[$service] ??= [];
            $attribute = $definition->attribute();
            if ($attribute !== null) {
                $message = sprintf('%s attributed with %s%s', $method, $definition->attribute()::class, PHP_EOL);
            } else {
                $message = sprintf('%s added with serviceDelegate()%s', $method, PHP_EOL);
            }
            $delegateMap[$service][] = $message;
        }

        foreach ($delegateMap as $service => $factories) {
            if (count($factories) > 1) {
                $factoryTypes = trim(implode('- ', $factories));
                $message = <<<TEXT
There are multiple delegates for the service "$service"!

- $factoryTypes

This will result in undefined behavior, determined by the backing container, and 
should be avoided.
TEXT;

                $violations->add(
                    LogicalConstraintViolation::warning($message)
                );
            }
        }

        return $violations;
    }
}
