<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateObserversInFavorOfEventSystem;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use JetBrains\PhpStorm\Deprecated;

/**
 * @deprecated
 */
#[
    DeprecateObserversInFavorOfEventSystem,
    Deprecated('Please see DeprecateObserversInFavorOfEventSystem ADR')
]
interface ContainerCreatedObserver {

    public function notifyContainerCreated(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void;

}