<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Definition\InjectTargetIdentifier;
use Cspray\Typiphy\ObjectType;

/**
 * @Internal
 */
final class MethodParameterInjectTargetIdentifier implements InjectTargetIdentifier {

    public function __construct(
        private readonly string $name,
        private readonly string $methodName,
        private readonly ObjectType $class
    ) {
    }

    public function isMethodParameter() : bool {
        return true;
    }

    public function isClassProperty() : bool {
        return false;
    }

    public function getName() : string {
        return $this->name;
    }

    public function getClass() : ObjectType {
        return $this->class;
    }

    public function getMethodName() : string {
        return $this->methodName;
    }
}
