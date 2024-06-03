<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects;

use Cspray\Typiphy\ObjectType;

final class ExpectedServicePrepare {

    public function __construct(
        public readonly ObjectType $type,
        public readonly string $method
    ) {
    }
}
