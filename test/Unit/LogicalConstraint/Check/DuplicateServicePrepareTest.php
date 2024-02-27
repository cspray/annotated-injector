<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServicePrepare;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServicePrepare\DummyPrepare;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\LogicalConstraintFixtures;
use function Cspray\Typiphy\objectType;

final class DuplicateServicePrepareTest extends LogicalConstraintTestCase {

    private ContainerDefinitionAnalyzer $analyzer;
    private DuplicateServicePrepare $subject;

    protected function setUp() : void {
        $this->analyzer = $this->getAnalyzer();
        $this->subject = new DuplicateServicePrepare();
    }

    public function testNoDuplicatePreparesHasZeroViolations() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::multiplePrepareServices()->getPath()
            )->build()
        );

        $results = $this->subject->getConstraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(0, $results);
    }

    public function testDuplicatePreparesHasViolation() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                LogicalConstraintFixtures::duplicateServicePrepare()->getPath()
            )->build()
        );

        $results = $this->subject->getConstraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $results);

        $violation = $results->get(0);
        $service = LogicalConstraintFixtures::duplicateServicePrepare()->fooService()->getName();
        $prepareAttr = ServicePrepare::class;
        $dummyAttr = DummyPrepare::class;

        $expected = <<<TEXT
The method "$service::postConstruct" has been defined to prepare multiple times!

- Attributed with $prepareAttr
- Attributed with $dummyAttr

This will result in undefined behavior, determined by the backing container, and 
should be avoided.

TEXT;


        self::assertSame(LogicalConstraintViolationType::Warning, $violation->violationType);
        self::assertSame(
            $expected,
            $violation->message
        );
    }

    public function testDuplicatePreparesWithDefinitionProviderHasViolation() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::singleConcreteService()->getPath()
            )->withDefinitionProvider(
                new class implements DefinitionProvider {
                    public function consume(DefinitionProviderContext $context) : void {
                        \Cspray\AnnotatedContainer\servicePrepare($context, objectType(
                            Fixtures::singleConcreteService()->fooImplementation()->getName()
                        ), 'postConstruct');
                        \Cspray\AnnotatedContainer\servicePrepare($context, objectType(
                            Fixtures::singleConcreteService()->fooImplementation()->getName()
                        ), 'postConstruct');
                    }
                }
            )->build()
        );

        $results = $this->subject->getConstraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $results);

        $violation = $results->get(0);
        $service = Fixtures::singleConcreteService()->fooImplementation()->getName();
        $expected = <<<TEXT
The method "$service::postConstruct" has been defined to prepare multiple times!

- Call to servicePrepare() in DefinitionProvider
- Call to servicePrepare() in DefinitionProvider

This will result in undefined behavior, determined by the backing container, and 
should be avoided.

TEXT;

        self::assertSame(LogicalConstraintViolationType::Warning, $violation->violationType);
        self::assertSame(
            $expected,
            $violation->message
        );
    }
}
