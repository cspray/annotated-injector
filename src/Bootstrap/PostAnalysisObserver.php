<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles;

/**
 * @deprecated
 */
interface PostAnalysisObserver {

    public function notifyPostAnalysis(Profiles $activeProfiles, ContainerDefinition $containerDefinition) : void;
}
