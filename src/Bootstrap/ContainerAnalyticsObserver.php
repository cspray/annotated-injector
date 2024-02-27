<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

/**
 * @deprecated
 */
interface ContainerAnalyticsObserver {

    public function notifyAnalytics(ContainerAnalytics $analytics) : void;
}
