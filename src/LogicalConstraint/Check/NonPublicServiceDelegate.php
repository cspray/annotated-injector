<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles;

final class NonPublicServiceDelegate implements LogicalConstraint {

    public function getConstraintViolations(ContainerDefinition $containerDefinition, Profiles $profiles) : LogicalConstraintViolationCollection {
        $violations = new LogicalConstraintViolationCollection();

        foreach ($containerDefinition->getServiceDelegateDefinitions() as $delegateDefinition) {
            $reflection = new \ReflectionMethod(sprintf('%s::%s', $delegateDefinition->getDelegateType()->getName(), $delegateDefinition->getDelegateMethod()));
            if ($reflection->isProtected() || $reflection->isPrivate()) {
                $protectedOrPrivate = $reflection->isProtected() ? 'protected' : 'private';
                $violations->add(
                    LogicalConstraintViolation::critical(
                        sprintf(
                            'A %s method, %s::%s, is marked as a service delegate. Service delegates MUST be marked public.',
                            $protectedOrPrivate,
                            $delegateDefinition->getDelegateType()->getName(),
                            $delegateDefinition->getDelegateMethod()
                        )
                    )
                );
            }
        }

        return $violations;
    }
}
