<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;
use PHPUnit\Framework\MockObject\MockObject;

trait HasMockDefinitions {

    abstract protected function createMock(string $class) : MockObject;

    /**
     * @param list<ServiceDefinition> $serviceDefinitions
     * @param list<ServiceDelegateDefinition> $serviceDelegateDefinitions
     * @param list<ServicePrepareDefinition> $servicePrepareDefinitions
     * @param list<InjectDefinition> $injectDefinitions
     * @Param list<AliasDefinition> $aliasDefinitions
     */
    private function containerDefinition(
        array $serviceDefinitions = [],
        array $serviceDelegateDefinitions = [],
        array $servicePrepareDefinitions = [],
        array $injectDefinitions = [],
        array $aliasDefinitions = [],
    ) : ContainerDefinition&MockObject {
        $mock = $this->createMock(ContainerDefinition::class);
        $mock->method('serviceDefinitions')->willReturn($serviceDefinitions);
        $mock->method('serviceDelegateDefinitions')->willReturn($serviceDelegateDefinitions);
        $mock->method('servicePrepareDefinitions')->willReturn($servicePrepareDefinitions);
        $mock->method('injectDefinitions')->willReturn($injectDefinitions);
        $mock->method('aliasDefinitions')->willReturn($aliasDefinitions);

        return $mock;
    }

    private function concreteServiceDefinition(
        Type $type,
        array $profiles = ['default'],
        ?string $name = null,
        bool $isPrimary = false,
    ) : ServiceDefinition {
        $mock = $this->createMock(ServiceDefinition::class);
        $mock->method('type')->willReturn($type);
        $mock->method('name')->willReturn($name);
        $mock->method('profiles')->willReturn($profiles);
        $mock->method('isPrimary')->willReturn($isPrimary);
        $mock->method('isConcrete')->willReturn(true);
        $mock->method('isAbstract')->willReturn(false);
        $serviceAttribute = $this->createMock(ServiceAttribute::class);
        $serviceAttribute->method('profiles')->willReturn([]);
        $serviceAttribute->method('isPrimary')->willReturn(false);
        $serviceAttribute->method('name')->willReturn(null);
        $mock->method('attribute')->willReturn($serviceAttribute);

        return $mock;
    }

    private function abstractServiceDefinition(
        Type $type,
        array $profiles = ['default']
    ) : ServiceDefinition {
        $mock = $this->createMock(ServiceDefinition::class);
        $mock->method('type')->willReturn($type);
        $mock->method('name')->willReturn(null);
        $mock->method('profiles')->willReturn($profiles);
        $mock->method('isPrimary')->willReturn(false);
        $mock->method('isConcrete')->willReturn(false);
        $mock->method('isAbstract')->willReturn(true);
        $serviceAttribute = $this->createMock(ServiceAttribute::class);
        $serviceAttribute->method('profiles')->willReturn([]);
        $serviceAttribute->method('isPrimary')->willReturn(false);
        $serviceAttribute->method('name')->willReturn(null);
        $mock->method('attribute')->willReturn($serviceAttribute);

        return $mock;
    }

    private function servicePrepareDefinition(
        Type $service,
        string $method
    ) : ServicePrepareDefinition {
        $mock = $this->createMock(ServicePrepareDefinition::class);
        $mock->method('service')->willReturn($service);
        $mock->method('methodName')->willReturn($method);
        $attribute = $this->createMock(ServicePrepareAttribute::class);
        $mock->method('attribute')->willReturn($attribute);

        return $mock;
    }

    private function serviceDelegateDefinition(
        Type $service,
        Type $factory,
        string $method,
        array $profiles = [],
    ) : ServiceDelegateDefinition {
        $mock = $this->createMock(ServiceDelegateDefinition::class);
        $mock->method('delegateType')->willReturn($factory);
        $mock->method('delegateMethod')->willReturn($method);
        $mock->method('serviceType')->willReturn($service);
        $mock->method('profiles')->willReturn($profiles);
        $attribute = $this->createMock(ServiceDelegateAttribute::class);
        $attribute->method('profiles')->willReturn([]);
        $attribute->method('service')->willReturn(null);
        $mock->method('attribute')->willReturn($attribute);

        return $mock;
    }

    private function aliasDefinition(Type $abstractService, Type $concreteService) : AliasDefinition {
        $mock = $this->createMock(AliasDefinition::class);
        $mock->method('abstractService')->willReturn($abstractService);
        $mock->method('concreteService')->willReturn($concreteService);

        return $mock;
    }

    private function injectDefinition(
        Type $service,
        string $method,
        string $parameter,
        Type|TypeUnion|TypeIntersect $type,
        mixed $value,
        array $profiles = ['default'],
        ?string $store = null
    ) {
        $mock = $this->createMock(InjectDefinition::class);
        $mock->method('class')->willReturn($service);
        $mock->method('methodName')->willReturn($method);
        $mock->method('parameterName')->willReturn($parameter);
        $mock->method('type')->willReturn($type);
        $mock->method('value')->willReturn($value);
        $mock->method('profiles')->willReturn($profiles);
        $mock->method('storeName')->willReturn($store);
        $attribute = $this->createMock(InjectAttribute::class);
        $attribute->method('value')->willReturn($value);
        $attribute->method('from')->willReturn($store);
        $attribute->method('profiles')->willReturn([]);
        $mock->method('attribute')->willReturn($attribute);

        return $mock;
    }
}
