<?php

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
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
     * Defines which code construct is the injection target.
     *
     * @return InjectTargetIdentifier
     */
    public function getTargetIdentifier() : InjectTargetIdentifier;

    /**
     * Returns the type of the method parameter or property that is being injected into.
     *
     * @return Type|TypeUnion|TypeIntersect
     */
    public function getType() : Type|TypeUnion|TypeIntersect;

    /**
     * The value that should be injected or passed to a ParameterStore defined by getStoreName() to derive the value
     * that should be injected.
     *
     * @return mixed
     */
    public function getValue() : mixed;

    /**
     * A list of profiles that have to be active for this InjectDefinition to be valid.
     *
     * @return list<non-empty-string>
     */
    public function getProfiles() : array;

    /**
     * The store name to retrieve the value from, or null if getValue() should be used directly.
     *
     * @return non-empty-string|null
     */
    public function getStoreName() : ?string;

    public function getAttribute() : ?InjectAttribute;
}
