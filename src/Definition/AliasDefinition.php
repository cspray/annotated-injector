<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Reflection\Type;

/**
 * Define the concrete Service that should be used when constructing an abstract Service.
 */
interface AliasDefinition {

    /**
     * An abstract Service used by your application but cannot be constructed directly.
     */
    public function abstractService() : Type;

    /**
     * The concrete Service that should be used where your applications requires the corresponding abstract Service.
     */
    public function concreteService() : Type;
}
