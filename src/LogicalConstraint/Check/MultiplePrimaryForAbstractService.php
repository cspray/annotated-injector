<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;
use Cspray\AnnotatedContainer\Profiles;
use Generator;

final class MultiplePrimaryForAbstractService implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition, Profiles $profiles) : LogicalConstraintViolationCollection {
        $violations = new LogicalConstraintViolationCollection();

        $abstractPrimaryMap = [];

        foreach ($this->getAbstractServices($containerDefinition) as $abstract) {
            $abstractService = $abstract->type()->getName();
            $abstractPrimaryMap[$abstractService] ??= [];
            $concreteServices = $this->getConcreteServicesInstanceOf($containerDefinition, $abstract);
            foreach ($concreteServices as $concrete) {
                if ($concrete->isPrimary()) {
                    $abstractPrimaryMap[$abstractService][] = $concrete->type()->getName() . PHP_EOL;
                }
            }
        }

        foreach ($abstractPrimaryMap as $abstract => $concreteServices) {
            if (count($concreteServices) > 1) {
                sort($concreteServices);
                $types = trim(implode('- ', $concreteServices));
                $message = <<<TEXT
The abstract service "$abstract" has multiple concrete services marked primary!

- $types

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

    /**
     * @return Generator<ServiceDefinition>
     */
    private function getAbstractServices(ContainerDefinition $containerDefinition) : Generator {
        foreach ($containerDefinition->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->isAbstract()) {
                yield $serviceDefinition;
            }
        }
    }

    /**
     * @return Generator<ServiceDefinition>
     */
    private function getConcreteServicesInstanceOf(ContainerDefinition $containerDefinition, ServiceDefinition $serviceDefinition) : Generator {
        foreach ($containerDefinition->serviceDefinitions() as $service) {
            if ($service->isConcrete()) {
                if (is_subclass_of($service->type()->getName(), $serviceDefinition->type()->getName())) {
                    yield $service;
                }
            }
        }
    }
}
