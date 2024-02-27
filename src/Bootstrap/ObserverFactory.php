<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

/**
 * @deprecated
 */
interface ObserverFactory {

    public function createObserver(string $observer) : PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver;
}
