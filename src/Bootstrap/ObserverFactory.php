<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateObserversInFavorOfEventSystem;
use JetBrains\PhpStorm\Deprecated;

#[
    DeprecateObserversInFavorOfEventSystem,
    Deprecated('Please see DeprecateObserversInFavorOfEventSystem ADR')
]
interface ObserverFactory {

    public function createObserver(string $observer) : PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver;
}
