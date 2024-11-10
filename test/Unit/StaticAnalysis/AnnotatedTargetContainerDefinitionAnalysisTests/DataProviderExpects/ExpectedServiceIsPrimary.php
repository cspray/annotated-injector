<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects;

use Cspray\AnnotatedContainer\Reflection\Type;

final class ExpectedServiceIsPrimary {

    public function __construct(public readonly Type $type, public readonly bool $isPrimary) {
    }
}
