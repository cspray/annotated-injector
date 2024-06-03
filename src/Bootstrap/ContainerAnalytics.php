<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\PrecisionStopwatch\Duration;

final class ContainerAnalytics {

    public function __construct(
        public readonly Duration $totalTime,
        public readonly Duration $timePreppingForAnalysis,
        public readonly Duration $timeTakenForAnalysis,
        public readonly Duration $timeTakenCreatingContainer
    ) {
    }
}
