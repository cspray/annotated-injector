<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

abstract class LogicalConstraintTestCase extends TestCase {

    protected function getAnalyzer() : ContainerDefinitionAnalyzer {
        return new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new Emitter()
        );
    }
}
