<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedObserver;
use Cspray\AnnotatedContainer\Bootstrap\PostAnalysisObserver;
use Cspray\AnnotatedContainer\Bootstrap\PreAnalysisObserver;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;

class StubBootstrapObserver implements PreAnalysisObserver, PostAnalysisObserver, ContainerCreatedObserver {

    private array $invokedMethods = [];

    public function notifyPreAnalysis(ActiveProfiles $activeProfiles) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function notifyPostAnalysis(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function notifyContainerCreated(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $this->invokedMethods[] = [__METHOD__];
    }

    public function getInvokedMethods() : array {
        return $this->invokedMethods;
    }
}
