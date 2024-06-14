<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use PHPUnit\Framework\Attributes\DataProvider;

trait HasServiceDefinitionTestsTrait {

    use ContainerDefinitionAssertionsTrait;

    abstract protected function getSubject() : ContainerDefinition;

    abstract public static function serviceTypeProvider() : array;

    abstract public static function serviceNameProvider() : array;

    abstract public static function serviceIsPrimaryProvider() : array;

    abstract public static function serviceIsConcreteProvider() : array;

    abstract public static function serviceIsAbstractProvider() : array;

    abstract public static function serviceProfilesProvider() : array;

    final public function testExpectedServiceTypeCount() : void {
        $expectedCount = count($this->serviceTypeProvider());

        $this->assertSame(
            $expectedCount,
            count($this->getSubject()->serviceDefinitions()),
            'The number of entries in \'serviceTypeProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceNameCount() : void {
        $expected = count($this->serviceNameProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->serviceDefinitions()),
            'The number of entries in \'serviceNameProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceIsPrimaryCount() : void {
        $expected = count($this->serviceIsPrimaryProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->serviceDefinitions()),
            'The number of entries in \'serviceIsPrimaryProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceIsConcreteCount() : void {
        $expected = count($this->serviceIsConcreteProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->serviceDefinitions()),
            'The number of entries in \'serviceIsConcreteProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceIsAbstractCount() : void {
        $expected = count($this->serviceIsAbstractProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->serviceDefinitions()),
            'The number of entries in \'serviceIsAbstractProvides\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceIsSharedCount() : void {
        $expected = count($this->serviceTypeProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->serviceDefinitions()),
            'The number of entries in \'serviceIsSharedProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceProfilesCount() : void {
        $expected = count($this->serviceProfilesProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->serviceDefinitions()),
            'The number of entries in \'serviceProfilesProvider\' does not match the number of service definitions.'
        );
    }

    #[DataProvider('serviceTypeProvider')]
    final public function testExpectedServiceTypes(ExpectedServiceType $expectedServiceType) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->serviceDefinitions(), $expectedServiceType->type->getName());

        $this->assertNotNull(
            $serviceDefinition,
            sprintf('Could not find a service that matches the expected type \'%s\'.', $expectedServiceType->type)
        );
    }

    #[DataProvider('serviceNameProvider')]
    final public function testExpectedServiceNames(ExpectedServiceName $expectedServiceName) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->serviceDefinitions(), $expectedServiceName->type->getName());

        $this->assertSame($expectedServiceName->name, $serviceDefinition?->name());
    }

    #[DataProvider('serviceIsPrimaryProvider')]
    final public function testExpectedServiceIsPrimary(ExpectedServiceIsPrimary $expectedServiceIsPrimary) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->serviceDefinitions(), $expectedServiceIsPrimary->type->getName());

        $this->assertSame($expectedServiceIsPrimary->isPrimary, $serviceDefinition?->isPrimary());
    }

    #[DataProvider('serviceIsConcreteProvider')]
    final public function testExpectedServiceIsConcrete(ExpectedServiceIsConcrete $expectedServiceIsConcrete) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->serviceDefinitions(), $expectedServiceIsConcrete->type->getName());

        $this->assertSame($expectedServiceIsConcrete->isConcrete, $serviceDefinition?->isConcrete());
    }

    #[DataProvider('serviceIsAbstractProvider')]
    final public function testExpectedServiceIsAbstract(ExpectedServiceIsAbstract $expectedServiceIsAbstract) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->serviceDefinitions(), $expectedServiceIsAbstract->type->getName());

        $this->assertSame($expectedServiceIsAbstract->isAbstract, $serviceDefinition?->isAbstract());
    }

    #[DataProvider('serviceProfilesProvider')]
    final public function testExpectedServiceProfiles(ExpectedServiceProfiles $expectedServiceProfiles) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->serviceDefinitions(), $expectedServiceProfiles->type->getName());

        $this->assertSame($expectedServiceProfiles->profiles, $serviceDefinition?->profiles());
    }
}
