<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles;

final class NonPublicServicePrepare implements LogicalConstraint {

    public function constraintViolations(ContainerDefinition $containerDefinition, Profiles $profiles) : LogicalConstraintViolationCollection {
        $violations =  new LogicalConstraintViolationCollection();

        foreach ($containerDefinition->servicePrepareDefinitions() as $prepareDefinition) {
            $reflection = new \ReflectionMethod(sprintf('%s::%s', $prepareDefinition->service()->name(), $prepareDefinition->classMethod()->methodName()));
            if ($reflection->isPrivate() || $reflection->isProtected()) {
                $protectedOrPrivate = $reflection->isProtected() ? 'protected' : 'private';
                $violations->add(
                    LogicalConstraintViolation::critical(
                        sprintf(
                            'A %s method, %s::%s, is marked as a service prepare. Service prepare methods MUST be marked public.',
                            $protectedOrPrivate,
                            $prepareDefinition->service()->name(),
                            $prepareDefinition->classMethod()->methodName()
                        )
                    )
                );
            }
        }

        return $violations;
    }
}
