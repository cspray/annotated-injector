<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint;

use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintValidator;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolation;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationCollection;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

class LogicalConstraintValidatorTest extends TestCase {

    private ContainerDefinitionAnalyzer $analyzer;

    protected function setUp(): void {
        $this->analyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
        );
    }

    public function testLogicalValidatorPassesContainerDefinitionToLogicalConstraintChecks() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::singleConcreteService()->getPath()
            )->build()
        );

        $mock = $this->getMockBuilder(LogicalConstraint::class)->getMock();
        $mock->expects($this->once())
            ->method('getConstraintViolations')
            ->with($definition, ['default'])
            ->willReturn(new LogicalConstraintViolationCollection());

        $subject = new LogicalConstraintValidator($mock);
        $results = $subject->validate($definition, ['default']);

        self::assertCount(0, $results);
    }

    public function testLogicalValidatorMergesLogicalConstraintViolations() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::singleConcreteService()->getPath()
            )->build()
        );

        $coll1 = new LogicalConstraintViolationCollection();
        $coll1->add(LogicalConstraintViolation::critical('message one'));
        $mock1 = $this->getMockBuilder(LogicalConstraint::class)->getMock();
        $mock1->expects($this->once())
            ->method('getConstraintViolations')
            ->with($definition, ['default'])
            ->willReturn($coll1);

        $coll2 = new LogicalConstraintViolationCollection();
        $coll2->add(LogicalConstraintViolation::warning('message two'));
        $mock2 = $this->getMockBuilder(LogicalConstraint::class)->getMock();
        $mock2->expects($this->once())
            ->method('getConstraintViolations')
            ->with($definition, ['default'])
            ->willReturn($coll2);

        $subject = new LogicalConstraintValidator($mock1, $mock2);
        $results = $subject->validate($definition, ['default']);

        self::assertCount(2, $results);
        self::assertSame('message one', $results->get(0)->message);
        self::assertSame(LogicalConstraintViolationType::Critical, $results->get(0)->violationType);

        self::assertSame('message two', $results->get(1)->message);
        self::assertSame(LogicalConstraintViolationType::Warning, $results->get(1)->violationType);
    }
}
