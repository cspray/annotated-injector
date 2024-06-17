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
     * @param class-string<T> $classType The FQCN for the type that should be created
     * @param AutowireableParameterSet|null $parameters A set of AutowireableParameters that should be used for constructor
     *                                                  arguments in place of or in addition to any autowire-resolved
     *                                                  parameters.
     * @return T
     */
    public function make(string $classType, AutowireableParameterSet $parameters = null) : object;
}
