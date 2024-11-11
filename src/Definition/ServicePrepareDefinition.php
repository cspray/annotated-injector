<?php declare(strict_types=1);


namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\Typiphy\ObjectType;

/**
 * Defines a method that should be invoked when the given type has been created by the Injector.
 *
 * @package Cspray\AnnotatedContainer
 */
interface ServicePrepareDefinition {

    /**
     * The Service that requires some method to be invoked on it after it is instantiated.
     */
    public function service() : Type;

    public function classMethod() : ClassMethod;

    public function attribute() : ServicePrepareAttribute;
}
