<?php

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;

/**
 * A definition that provides details on values that should be injected into method parameters or Configuration properties
 * that can't be implicitly derived through static analysis.
 *
 * @see InjectDefinitionBuilder
 */
interface InjectDefinition {

    /**
     * Returns the type of the method parameter or property that is being injected into.
     *
     * @return Type|TypeUnion|TypeIntersect
     */
    public function type() : Type|TypeUnion|TypeIntersect;

    /**
     * The value that should be injected or passed to a ParameterStore defined by getStoreName() to derive the value
     * that should be injected.
     *
     * @return mixed
     */
    public function value() : mixed;

    /**
     * A list of profiles that have to be active for this InjectDefinition to be valid.
     *
     * @return list<non-empty-string>
     */
    public function profiles() : array;

    /**
     * The store name to retrieve the value from, or null if getValue() should be used directly.
     *
     * @return non-empty-string|null
     */
    public function storeName() : ?string;

    public function attribute() : ?InjectAttribute;

    /**
     * The class that has the method being injected into.
     *
     * @return ObjectType
     */
    public function class() : ObjectType;

    /**
     * @return non-empty-string
     */
    public function methodName() : string;

    /**
     * The name of the parameter or property that should have a value injected.
     *
     * @return non-empty-string
     */
    public function parameterName() : string;
}
