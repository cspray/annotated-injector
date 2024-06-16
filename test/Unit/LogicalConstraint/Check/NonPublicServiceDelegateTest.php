<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\LogicalConstraintFixtures;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\PrivateServiceDelegateMethod\PrivateFooServiceFactory;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\ProtectedServiceDelegateMethod\ProtectedFooServiceFactory;

final class NonPublicServiceDelegateTest extends LogicalConstraintTestCase {

    private ContainerDefinitionAnalyzer $analyzer;
    private NonPublicServiceDelegate $subject;

    protected function setUp(): void {
        $this->analyzer = $this->getAnalyzer();
        $this->subject = new NonPublicServiceDelegate();
    }

    public function testServiceDelegateIsPublicMethodHasNoLogicalConstraints() : void {
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            Fixtures::implicitServiceDelegateType()->getPath()
        )->build();

        $definition = $this->analyzer->analyze($options);

        $violations = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(0, $violations);
    }

    public function testServiceDelegateIsProtectedMethodHasCorrectLogicalConstraint() : void {
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            LogicalConstraintFixtures::protectedServiceDelegate()->getPath()
        )->build();

        $definition = $this->analyzer->analyze($options);

        $violations = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $violations);
        self::assertSame(LogicalConstraintViolationType::Critical, $violations->get(0)->violationType);
        self::assertSame(
            'A protected method, ' . ProtectedFooServiceFactory::class . '::createFoo, is marked as a service delegate. Service delegates MUST be marked public.',
            $violations->get(0)->message
        );
    }

    public function testServiceDelegateIsPrivateMethodHasCorrectLogicalConstraint() : void {
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            LogicalConstraintFixtures::privateServiceDelegate()->getPath()
        )->build();

        $definition = $this->analyzer->analyze($options);

        $violations = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $violations);
        self::assertSame(LogicalConstraintViolationType::Critical, $violations->get(0)->violationType);
        self::assertSame(
            'A private method, ' . PrivateFooServiceFactory::class . '::createFoo, is marked as a service delegate. Service delegates MUST be marked public.',
            $violations->get(0)->message
        );
    }
}
