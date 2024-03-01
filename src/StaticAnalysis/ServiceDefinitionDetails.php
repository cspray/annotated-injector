<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedTarget\AnnotatedTarget;

final class ServiceDefinitionDetails {

    public function __construct(
        public readonly AnnotatedTarget $annotatedTarget,
        public readonly ServiceDefinition $serviceDefinition,
    ) {}

}