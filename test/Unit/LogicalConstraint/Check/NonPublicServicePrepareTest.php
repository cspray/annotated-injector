<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServicePrepare;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\LogicalConstraintFixtures;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\PrivateServicePrepareMethod\FooService as PrivateFooService;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\ProtectedServicePrepareMethod\FooService as ProtectedFooService;

final class NonPublicServicePrepareTest extends LogicalConstraintTestCase {

    private ContainerDefinitionAnalyzer $analyzer;

    private NonPublicServicePrepare $subject;

    protected function setUp() : void {
        $this->analyzer = $this->getAnalyzer();
        $this->subject = new NonPublicServicePrepare();
    }

    public function testServicePrepareIsPublicMethodHasNoViolations() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::interfacePrepareServices()->getPath()
            )->build()
        );

        $collection = $this->subject->getConstraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(0, $collection);
    }

    public function testServicePrepareIsPrivateMethodHasCorrectViolation() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                LogicalConstraintFixtures::privateServicePrepare()->getPath()
            )->build()
        );

        $collection = $this->subject->getConstraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $collection);

        $violation = $collection->get(0);

        self::assertSame(
            'A private method, ' . PrivateFooService::class . '::postConstruct, is marked as a service prepare. Service prepare methods MUST be marked public.',
            $violation->message
        );
        self::assertSame(LogicalConstraintViolationType::Critical, $violation->violationType);
    }

    public function testServicePrepareIsProtectedMethodHasCorrectViolation() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                LogicalConstraintFixtures::protectedServicePrepare()->getPath()
            )->build()
        );

        $collection = $this->subject->getConstraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $collection);

        $violation = $collection->get(0);

        self::assertSame(
            'A protected method, ' . ProtectedFooService::class . '::postConstruct, is marked as a service prepare. Service prepare methods MUST be marked public.',
            $violation->message
        );
        self::assertSame(LogicalConstraintViolationType::Critical, $violation->violationType);
    }
}
