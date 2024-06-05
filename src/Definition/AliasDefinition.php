<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\Typiphy\ObjectType;

/**
 * Define the concrete Service that should be used when constructing an abstract Service.
 *
 * @see AliasDefinitionBuilder
 */
interface AliasDefinition {

    /**
     * An abstract Service used by your application but cannot be constructed directly.
     *
     * @return ObjectType
     */
    public function abstractService() : ObjectType;

    /**
     * The concrete Service that should be used where your applications requires the corresponding abstract Service.
     *
     * @return ObjectType
     */
    public function concreteService() : ObjectType;
}
