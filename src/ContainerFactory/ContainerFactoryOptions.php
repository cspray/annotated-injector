<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Profiles;
use Psr\Log\LoggerInterface;

/**
 * A set of options used by a ContainerFactory when creating your Container.
 *
 * @see ContainerFactoryOptionsBuilder
 */
interface ContainerFactoryOptions {

    /**
     * A list of profiles that should be considered active.
     *
     * @return Profiles
     */
    public function getProfiles() : Profiles;

    /**
     * @deprecated
     */
    public function getLogger() : ?LoggerInterface;
}
