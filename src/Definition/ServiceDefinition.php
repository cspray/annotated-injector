<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\Typiphy\ObjectType;

/**
 * Defines a Service, a class or interface that should be shared or aliased on the wired Injector.
 *
 * @see ServiceDefinitionBuilder
 */
interface ServiceDefinition {

    /**
     * @return non-empty-string|null
     */
    public function getName() : ?string;

    /**
     * Returns the fully-qualified class/interface name for the given Service.
     *
     * @return ObjectType
     */
    public function getType() : ObjectType;

    /**
     * Returns an array of profiles that this service is attached to.
     *
     * A ServiceDefinition MUST have at least 1 profile; if a profile is not explicitly set for a given Service it should
     * be given the 'default' profile.
     *
     * @return list<non-empty-string>
     */
    public function getProfiles() : array;

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

    public function getAttribute() : ?ServiceAttribute;
}
