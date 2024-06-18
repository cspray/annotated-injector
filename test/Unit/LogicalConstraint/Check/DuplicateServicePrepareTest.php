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
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServicePrepare\DummyPrepare;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\LogicalConstraintFixtures;
use function Cspray\Typiphy\objectType;
use function Cspray\AnnotatedContainer\servicePrepare;

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

        $results = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(0, $results);
    }

    public function testDuplicatePreparesHasViolation() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                LogicalConstraintFixtures::duplicateServicePrepare()->getPath()
            )->build()
        );

        $results = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $results);

        $violation = $results->get(0);
        $service = LogicalConstraintFixtures::duplicateServicePrepare()->fooService()->name();
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
                        $context->addServicePrepareDefinition(
                            servicePrepare(objectType(
                                Fixtures::singleConcreteService()->fooImplementation()->name()
                            ), 'postConstruct')
                        );
                        $context->addServicePrepareDefinition(
                            servicePrepare(objectType(
                                Fixtures::singleConcreteService()->fooImplementation()->name()
                            ), 'postConstruct')
                        );
                    }
                }
            )->build()
        );

        $results = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $results);

        $violation = $results->get(0);
        $service = Fixtures::singleConcreteService()->fooImplementation()->name();
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
