<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedTarget\AnnotatedTarget;

final class ServicePrepareDefinitionDetails {

    public function __construct(
        public readonly AnnotatedTarget $target,
        public readonly ServicePrepareDefinition $servicePrepareDefinition,
    ) {}

}