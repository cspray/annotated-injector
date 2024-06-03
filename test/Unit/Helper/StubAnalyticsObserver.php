<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalyticsObserver;

final class StubAnalyticsObserver implements ContainerAnalyticsObserver {

    public static ?ContainerAnalytics $analytics = null;

    public function notifyAnalytics(ContainerAnalytics $analytics) : void {
        self::$analytics = $analytics;
    }
}
