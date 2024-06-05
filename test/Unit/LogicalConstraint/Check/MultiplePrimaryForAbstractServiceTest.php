<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\LogicalConstraint\Check\MultiplePrimaryForAbstractService;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\LogicalConstraintFixtures;
use PHPUnit\Framework\TestCase;

final class MultiplePrimaryForAbstractServiceTest extends LogicalConstraintTestCase {

    private ContainerDefinitionAnalyzer $analyzer;

    private MultiplePrimaryForAbstractService $subject;

    protected function setUp() : void {
        $this->analyzer = $this->getAnalyzer();
        $this->subject = new MultiplePrimaryForAbstractService();
    }

    public function testSinglePrimaryPerServiceHasNoViolations() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::primaryAliasedServices()->getPath()
            )->build()
        );

        $violations = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(0, $violations);
    }

    public function testMultiplePrimaryServiceAttributedHasCorrectViolation() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                LogicalConstraintFixtures::multiplePrimaryService()->getPath()
            )->build()
        );

        $violations = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $violations);

        $violation = $violations->get(0);
        $abstractService = LogicalConstraintFixtures::multiplePrimaryService()->fooInterface()->getName();
        $fooService = LogicalConstraintFixtures::multiplePrimaryService()->fooService()->getName();
        $barService = LogicalConstraintFixtures::multiplePrimaryService()->barService()->getName();

        $expected = <<<TEXT
The abstract service "$abstractService" has multiple concrete services marked primary!

- $barService
- $fooService

This will result in undefined behavior, determined by the backing container, and
should be avoided.
TEXT;

        self::assertSame(
            LogicalConstraintViolationType::Warning,
            $violation->violationType
        );
        self::assertSame(
            $expected,
            $violation->message
        );
    }
}
