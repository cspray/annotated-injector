<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\Typiphy\ObjectType;

interface InjectTargetIdentifier {

    public function isMethodParameter() : bool;

    public function isClassProperty() : bool;

    /**
     * The name of the parameter or property that should have a value injected.
     *
     * @return non-empty-string
     */
    public function name() : string;

    public function class() : ObjectType;

    /**
     * @return non-empty-string|null
     */
    public function methodName() : ?string;
}
