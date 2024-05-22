<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateObserversInFavorOfEventSystem;
use JetBrains\PhpStorm\Deprecated;

/**
 * @deprecated
 */
#[
    DeprecateObserversInFavorOfEventSystem,
    Deprecated('Please see DeprecateObserversInFavorOfEventSystem ADR')
]
interface ContainerAnalyticsObserver {

    public function notifyAnalytics(ContainerAnalytics $analytics) : void;

}
