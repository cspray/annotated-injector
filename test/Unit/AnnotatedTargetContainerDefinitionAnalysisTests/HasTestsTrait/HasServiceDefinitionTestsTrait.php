<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Unit\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;

trait HasServiceDefinitionTestsTrait {

    use ContainerDefinitionAssertionsTrait;

    abstract protected function getSubject() : ContainerDefinition;

    abstract protected function serviceTypeProvider() : array;

    abstract protected function serviceNameProvider() : array;

    abstract protected function serviceIsPrimaryProvider() : array;

    abstract protected function serviceIsConcreteProvider() : array;

    abstract protected function serviceIsAbstractProvider() : array;

    abstract protected function serviceProfilesProvider() : array;

    final public function testExpectedServiceTypeCount() : void {
        $expectedCount = count($this->serviceTypeProvider());

        $this->assertSame(
            $expectedCount,
            count($this->getSubject()->getServiceDefinitions()),
            'The number of entries in \'serviceTypeProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceNameCount() : void {
        $expected = count($this->serviceNameProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->getServiceDefinitions()),
            'The number of entries in \'serviceNameProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceIsPrimaryCount() : void {
        $expected = count($this->serviceIsPrimaryProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->getServiceDefinitions()),
            'The number of entries in \'serviceIsPrimaryProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceIsConcreteCount() : void {
        $expected = count($this->serviceIsConcreteProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->getServiceDefinitions()),
            'The number of entries in \'serviceIsConcreteProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceIsAbstractCount() : void {
        $expected = count($this->serviceIsAbstractProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->getServiceDefinitions()),
            'The number of entries in \'serviceIsAbstractProvides\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceIsSharedCount() : void {
        $expected = count($this->serviceTypeProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->getServiceDefinitions()),
            'The number of entries in \'serviceIsSharedProvider\' does not match the number of service definitions.'
        );
    }

    final public function testExpectedServiceProfilesCount() : void {
        $expected = count($this->serviceProfilesProvider());

        $this->assertSame(
            $expected,
            count($this->getSubject()->getServiceDefinitions()),
            'The number of entries in \'serviceProfilesProvider\' does not match the number of service definitions.'
        );
    }

    /**
     * @dataProvider serviceTypeProvider
     */
    final public function testExpectedServiceTypes(ExpectedServiceType $expectedServiceType) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->getServiceDefinitions(), $expectedServiceType->type->getName());

        $this->assertNotNull(
            $serviceDefinition,
            sprintf('Could not find a service that matches the expected type \'%s\'.', $expectedServiceType->type)
        );
    }

    /**
     * @dataProvider serviceNameProvider
     */
    final public function testExpectedServiceNames(ExpectedServiceName $expectedServiceName) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->getServiceDefinitions(), $expectedServiceName->type->getName());

        $this->assertSame($expectedServiceName->name, $serviceDefinition?->getName());
    }

    /**
     * @dataProvider serviceIsPrimaryProvider
     */
    final public function testExpectedServiceIsPrimary(ExpectedServiceIsPrimary $expectedServiceIsPrimary) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->getServiceDefinitions(), $expectedServiceIsPrimary->type->getName());

        $this->assertSame($expectedServiceIsPrimary->isPrimary, $serviceDefinition?->isPrimary());
    }

    /**
     * @dataProvider serviceIsConcreteProvider
     */
    final public function testExpectedServiceIsConcrete(ExpectedServiceIsConcrete $expectedServiceIsConcrete) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->getServiceDefinitions(), $expectedServiceIsConcrete->type->getName());

        $this->assertSame($expectedServiceIsConcrete->isConcrete, $serviceDefinition?->isConcrete());
    }

    /**
     * @dataProvider serviceIsAbstractProvider
     */
    final public function testExpectedServiceIsAbstract(ExpectedServiceIsAbstract $expectedServiceIsAbstract) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->getServiceDefinitions(), $expectedServiceIsAbstract->type->getName());

        $this->assertSame($expectedServiceIsAbstract->isAbstract, $serviceDefinition?->isAbstract());
    }

    /**
     * @dataProvider serviceProfilesProvider
     */
    final public function testExpectedServiceProfiles(ExpectedServiceProfiles $expectedServiceProfiles) : void {
        $serviceDefinition = $this->getServiceDefinition($this->getSubject()->getServiceDefinitions(), $expectedServiceProfiles->type->getName());

        $this->assertSame($expectedServiceProfiles->profiles, $serviceDefinition?->getProfiles());
    }
}
