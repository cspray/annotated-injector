<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateObserversInFavorOfEventSystem;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use JetBrains\PhpStorm\Deprecated;

/**
 * @deprecated
 */
#[
    DeprecateObserversInFavorOfEventSystem,
    Deprecated('Please see DeprecateObserversInFavorOfEventSystem ADR')
]
interface PreAnalysisObserver {

    public function notifyPreAnalysis(ActiveProfiles $activeProfiles) : void;

}