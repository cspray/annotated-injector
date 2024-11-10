<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Reflection\Type;

/**
 * Defines an object that will be shared in the created AnnotatedContainer.
 */
interface ServiceDefinition {

    /**
     * Returns the fully-qualified class/interface name for the given Service.
     *
     */
    public function type() : Type;

    /**
     * @return non-empty-string|null
     */
    public function name() : ?string;

    /**
     * Returns an array of profiles that this service is attached to.
     *
     * A ServiceDefinition MUST have at least 1 profile; if a profile is not explicitly set for a given Service it should
     * be given the 'default' profile.
     *
     * @return list<non-empty-string>
     */
    public function profiles() : array;

    /**
     * Return whether the Service is the Primary for this type and will be used by default if there are multiple aliases
     * resolved.
     *
     * @return bool
     */
    public function isPrimary() : bool;

    /**
     * Returns whether the defined Service is a concrete class that can be instantiated.
     *
     * @return bool
     */
    public function isConcrete() : bool;

    /**
     * Returns whether the defined Service is an abstract class or interface that cannot be instantiated.
     *
     * @return bool
     */
    public function isAbstract() : bool;

    public function attribute() : ServiceAttribute;
}
