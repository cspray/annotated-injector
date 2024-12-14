<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Reflection\Type;

/**
 * Defines a factory method for creating a specific type of Service.
 */
interface ServiceDelegateDefinition {

    public function service() : Type;

    public function classMethod() : ClassMethod;

    /**
     * @return list<non-empty-string>
     */
    public function profiles() : array;

    public function attribute() : ServiceDelegateAttribute;
}
