<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\LogicalConstraintFixtures;
use function Cspray\AnnotatedContainer\Definition\serviceDelegate;

final class DuplicateServiceDelegateTest extends LogicalConstraintTestCase {

    private ContainerDefinitionAnalyzer $analyzer;

    private DuplicateServiceDelegate $subject;

    protected function setUp() : void {
        $this->analyzer = $this->getAnalyzer();
        $this->subject = new DuplicateServiceDelegate();
    }

    public function testNoDuplicateDelegateHasNoViolations() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::implicitServiceDelegateType()->getPath()
            )->build()
        );

        $violations = $this->subject->constraintViolations($definition, Profiles::defaultOnly());

        self::assertCount(0, $violations);
    }

    public function testDuplicateDelegateAttributeForSameServiceHasCorrectViolation() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                LogicalConstraintFixtures::duplicateServiceDelegate()->getPath()
            )->build()
        );

        $violations = $this->subject->constraintViolations($definition, Profiles::defaultOnly());

        self::assertCount(1, $violations);

        $violation = $violations->get(0);
        $fooService = LogicalConstraintFixtures::duplicateServiceDelegate()->fooService()->name();
        $factory = LogicalConstraintFixtures::duplicateServiceDelegate()->factory()->name();
        $serviceDelegate = ServiceDelegate::class;

        $expected = <<<TEXT
There are multiple delegates for the service "$fooService"!

- $factory::createFoo attributed with $serviceDelegate
- $factory::createFooAgain attributed with $serviceDelegate

This will result in undefined behavior, determined by the backing container, and 
should be avoided.
TEXT;

        self::assertSame(LogicalConstraintViolationType::Warning, $violation->violationType);
        self::assertSame($expected, $violation->message);
    }

    public function testDuplicateDelegateAddedWithFunctionalApi() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::implicitServiceDelegateType()->getPath()
            )->withDefinitionProvider(
                new class implements DefinitionProvider {

                    public function consume(DefinitionProviderContext $context) : void {
                        $context->addServiceDelegateDefinition(
                            serviceDelegate(
                                Fixtures::implicitServiceDelegateType()->fooServiceFactory(),
                                'create'
                            )
                        );
                    }
                }
            )->build()
        );

        $violations = $this->subject->constraintViolations($definition, Profiles::defaultOnly());

        self::assertCount(1, $violations);

        $violation = $violations->get(0);

        $fooService = Fixtures::implicitServiceDelegateType()->fooService()->name();
        $factory = Fixtures::implicitServiceDelegateType()->fooServiceFactory()->name();
        $serviceDelegate = ServiceDelegate::class;

        $expected = <<<TEXT
There are multiple delegates for the service "$fooService"!

- $factory::create added with serviceDelegate()
- $factory::create attributed with $serviceDelegate

This will result in undefined behavior, determined by the backing container, and 
should be avoided.
TEXT;

        self::assertSame(LogicalConstraintViolationType::Warning, $violation->violationType);
        self::assertSame($expected, $violation->message);
    }
}
