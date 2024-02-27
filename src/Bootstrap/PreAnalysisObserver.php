<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Profiles;

/**
 * @deprecated
 */
interface PreAnalysisObserver {

    public function notifyPreAnalysis(Profiles $activeProfiles) : void;
}
