<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Bootstrap\PreAnalysisObserver;
use Cspray\AnnotatedContainer\Profiles;

final class StubBootstrapObserverWithDependencies implements PreAnalysisObserver {

    public function __construct(public readonly string $myString) {
    }

    public function notifyPreAnalysis(Profiles $activeProfiles) : void {
    }
}
