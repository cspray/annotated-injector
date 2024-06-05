<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Definition\InjectTargetIdentifier;
use Cspray\Typiphy\ObjectType;

/**
 * @Internal
 */
final class PropertyInjectTargetIdentifier implements InjectTargetIdentifier {
    /**
     * @param non-empty-string $name
     * @param ObjectType $class
     */
    public function __construct(
        private readonly string $name,
        private readonly ObjectType $class
    ) {
    }

    public function isMethodParameter() : bool {
        return false;
    }

    public function isClassProperty() : bool {
        return true;
    }

    public function name() : string {
        return $this->name;
    }

    public function class() : ObjectType {
        return $this->class;
    }

    public function methodName() : ?string {
        return null;
    }
}
