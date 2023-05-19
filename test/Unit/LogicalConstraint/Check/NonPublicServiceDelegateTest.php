<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\Unit\LogicalErrorApps\PrivateServiceDelegateMethod\PrivateFooServiceFactory;
use Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ProtectedServiceDelegateMethod\FooService;
use Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ProtectedServiceDelegateMethod\ProtectedFooServiceFactory;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

final class NonPublicServiceDelegateTest extends TestCase {

    private ContainerDefinitionAnalyzer $analyzer;
    private NonPublicServiceDelegate $subject;

    protected function setUp(): void {
        $this->analyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
        );
        $this->subject = new NonPublicServiceDelegate();
    }

    public function testServiceDelegateIsPublicMethodHasNoLogicalConstraints() : void {
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            Fixtures::implicitServiceDelegateType()->getPath()
        )->build();

        $definition = $this->analyzer->analyze($options);

        $violations = $this->subject->getConstraintViolations($definition);

        self::assertCount(0, $violations);
    }

    public function testServiceDelegateIsProtectedMethodHasCorrectLogicalConstraint() : void {
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            dirname(__DIR__, 2) . '/LogicalErrorApps/ProtectedServiceDelegateMethod'
        )->build();

        $definition = $this->analyzer->analyze($options);

        $violations = $this->subject->getConstraintViolations($definition);

        self::assertCount(1, $violations);
        self::assertSame(LogicalConstraintViolationType::Critical, $violations->get(0)->violationType);
        self::assertSame(
            'A protected method, ' . ProtectedFooServiceFactory::class . '::createFoo, is marked as a service delegate. Service delegates MUST be marked public.',
            $violations->get(0)->message
        );
    }

    public function testServiceDelegateIsPrivateMethodHasCorrectLogicalConstraint() : void {
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            dirname(__DIR__, 2) . '/LogicalErrorApps/PrivateServiceDelegateMethod'
        )->build();

        $definition = $this->analyzer->analyze($options);

        $violations = $this->subject->getConstraintViolations($definition);

        self::assertCount(1, $violations);
        self::assertSame(LogicalConstraintViolationType::Critical, $violations->get(0)->violationType);
        self::assertSame(
            'A private method, ' . PrivateFooServiceFactory::class . '::createFoo, is marked as a service delegate. Service delegates MUST be marked public.',
            $violations->get(0)->message
        );
    }

}
