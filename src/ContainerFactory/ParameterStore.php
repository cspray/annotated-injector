<?php

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;

/**
 * Defines an implementation that can programmatically determine values that should be used with Inject definitions.
 */
interface ParameterStore {

    /**
     * The name of the store; Inject definitions that use this string in their from argument will use this ParameterStore.
     *
     * @return non-empty-string
     */
    public function name() : string;

    /**
     * Retrieve the value appropriate for $key.
     *
     * Information about the type for the method parameter or property is provided to be able to create different values
     * or reject the fetch if the type is incompatible with this store.
     *
     * @param Type|TypeUnion|TypeIntersect $type
     * @param string $key
     * @return mixed
     */
    public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed;
}
