<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalConstraint\Check;

use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceName;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceName\BarService;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\DuplicateServiceName\FooService;
use Cspray\AnnotatedContainerFixture\LogicalConstraints\LogicalConstraintFixtures;

final class DuplicateServiceNameTest extends LogicalConstraintTestCase {

    private ContainerDefinitionAnalyzer $analyzer;

    private DuplicateServiceName $subject;

    protected function setUp() : void {
        $this->analyzer = $this->getAnalyzer();
        $this->subject = new DuplicateServiceName();
    }

    public function testServiceWithMultipleNamesReturnsCorrectViolation() : void {
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            LogicalConstraintFixtures::duplicateServiceName()->getPath()
        )->build();

        $definition = $this->analyzer->analyze($options);

        $violations = $this->subject->getConstraintViolations($definition, ['default']);

        $barService = BarService::class;
        $fooService = FooService::class;
        $expectedMessage = <<<TEXT
There are multiple services with the name "foo". The service types are:

- $barService
- $fooService
TEXT;
        $actualMessage = $violations->get(0)->message;


        self::assertCount(1, $violations);
        self::assertSame(LogicalConstraintViolationType::Critical, $violations->get(0)->violationType);
        self::assertSame(trim($expectedMessage), $actualMessage);
    }

    public static function duplicateServiceNameProfiles() : array {
        return [
            ['prod'],
            ['dev']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('duplicateServiceNameProfiles')]
    public function testServiceWithMultipleNamesOnDifferentProfilesHasNoViolation(string $profile) : void {
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
            Fixtures::duplicateNamedServiceDifferentProfiles()->getPath()
        )->build();

        $definition = $this->getAnalyzer()->analyze($options);

        $violations = $this->subject->getConstraintViolations($definition, [$profile]);

        self::assertCount(0, $violations);
    }
}
