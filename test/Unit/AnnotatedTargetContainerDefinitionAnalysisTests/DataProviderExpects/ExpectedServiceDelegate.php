<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects;

use Cspray\Typiphy\ObjectType;

final class ExpectedServiceDelegate {

    public function __construct(
        public readonly ObjectType $service,
        public readonly ObjectType $factory,
        public readonly string $method
    ) {
    }
}
