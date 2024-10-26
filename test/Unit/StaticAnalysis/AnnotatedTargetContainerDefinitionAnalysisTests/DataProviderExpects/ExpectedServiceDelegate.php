<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects;

use Cspray\AnnotatedContainer\Reflection\Type;

final class ExpectedServiceDelegate {

    public function __construct(
        public readonly Type $service,
        public readonly Type $factory,
        public readonly string $method
    ) {
    }
}
