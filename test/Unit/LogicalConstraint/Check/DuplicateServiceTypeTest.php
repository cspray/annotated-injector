<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceType;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceType\DummyService;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceType\FooService as DuplicateAttributeFooService;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\LogicalConstraintFixtures;
use Cspray\AnnotatedContainer\Fixture\NonAnnotatedServices\NotAnnotatedObject;
use function Cspray\AnnotatedContainer\Definition\service;
use function Cspray\AnnotatedContainer\Reflection\types;

final class DuplicateServiceTypeTest extends LogicalConstraintTestCase {

    private ContainerDefinitionAnalyzer $analyzer;

    private DuplicateServiceType $subject;

    protected function setUp() : void {
        $this->analyzer = $this->getAnalyzer();
        $this->subject = new DuplicateServiceType();
    }

    public function testNoDuplicateServicesHasNoViolations() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::profileResolvedServices()->getPath()
            )->build()
        );

        $results = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(0, $results);
    }

    public function testDuplicateServiceTypesWithAttributes() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                LogicalConstraintFixtures::duplicateServiceType()->getPath()
            )->build()
        );

        $results = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $results);

        $violation = $results->get(0);

        $fooService = DuplicateAttributeFooService::class;
        $service = Service::class;
        $dummyService = DummyService::class;

        $expected = <<<TEXT
The type "$fooService" has been defined multiple times!

- Attributed with $service
- Attributed with $dummyService

This will result in undefined behavior, determined by the backing container, and 
should be avoided.

TEXT;

        self::assertSame(LogicalConstraintViolationType::Warning, $violation->violationType);
        self::assertSame($expected, $violation->message);
    }

    public function testDuplicateServiceTypesWithOnlyMultipleFunctionCalls() : void {
        $definition = $this->analyzer->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                Fixtures::nonAnnotatedServices()->getPath()
            )->withDefinitionProvider(
                new class implements DefinitionProvider {
                    public function consume(DefinitionProviderContext $context) : void {
                        $context->addServiceDefinition(service(types()->class(NotAnnotatedObject::class)));
                        $context->addServiceDefinition(service(types()->class(NotAnnotatedObject::class)));
                    }
                }
            )->build()
        );

        $results = $this->subject->constraintViolations($definition, Profiles::fromList(['default']));

        self::assertCount(1, $results);

        $violation = $results->get(0);

        $service = NotAnnotatedObject::class;

        $expected = <<<TEXT
The type "$service" has been defined multiple times!

- Call to service() in DefinitionProvider
- Call to service() in DefinitionProvider

This will result in undefined behavior, determined by the backing container, and 
should be avoided.

TEXT;

        self::assertSame(LogicalConstraintViolationType::Warning, $violation->violationType);
        self::assertSame($expected, $violation->message);
    }
}
