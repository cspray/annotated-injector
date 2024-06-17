<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Autowire;

/**
 * An interface that allows recursively autowiring object construction for classes that have not been annotated as a
 * Service.
 */
interface AutowireableFactory {

    /**
     * Construct an object matching $classType, autowiring any parameters that were not provided as part of the
     * AutowireableParameterSet.
     *
     * @template T of object
     * @psalm-param class-string<T> $classType
     * @psalm-return T
     */
    public function make(string $classType, AutowireableParameterSet $parameters = null) : object;
}
