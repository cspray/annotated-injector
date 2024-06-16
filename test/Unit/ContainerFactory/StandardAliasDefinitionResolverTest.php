<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactory;

use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasResolutionReason;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\StandardAliasDefinitionResolver;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use PHPUnit\Framework\TestCase;

final class StandardAliasDefinitionResolverTest extends TestCase {

    public function testPassAbstractServiceDefinitionWithNoConcreteDefinitionReturnsCorrectResolution() : void {
        $subject = new StandardAliasDefinitionResolver();
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($serviceDefinition)
            ->build();

        $resolution = $subject->resolveAlias($containerDefinition, $serviceDefinition->type());

        self::assertSame(AliasResolutionReason::NoConcreteService, $resolution->aliasResolutionReason());
        self::assertNull($resolution->aliasDefinition());
    }

    public function testPassAbstractServiceDefinitionWithSingleConcreteDefinitionReturnsCorrectResolution() : void {
        $subject = new StandardAliasDefinitionResolver();
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $concrete1 = ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
            ->build();
        $concrete2 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())
            ->build();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($serviceDefinition)
            ->withServiceDefinition($concrete1)
            ->withServiceDefinition($concrete2)
            ->withAliasDefinition(
                $aliasDefinition = AliasDefinitionBuilder::forAbstract($serviceDefinition->type())
                    ->withConcrete($concrete2->type())
                    ->build()
            )->build();

        $resolution = $subject->resolveAlias($containerDefinition, $serviceDefinition->type());

        self::assertSame(AliasResolutionReason::SingleConcreteService, $resolution->aliasResolutionReason());
        self::assertSame($aliasDefinition, $resolution->aliasDefinition());
    }

    public function testPassAbstractServiceDefinitionWithMultipleConcreteDefinitionReturnsCorrectResolution() : void {
        $subject = new StandardAliasDefinitionResolver();
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $concrete1 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->bazImplementation())
            ->build();
        $concrete2 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())
            ->build();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($serviceDefinition)
            ->withServiceDefinition($concrete1)
            ->withServiceDefinition($concrete2)
            ->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($serviceDefinition->type())
                    ->withConcrete($concrete1->type())
                    ->build()
            )->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($serviceDefinition->type())
                    ->withConcrete($concrete2->type())
                    ->build()
            )->build();

        $resolution = $subject->resolveAlias($containerDefinition, $serviceDefinition->type());

        self::assertSame(AliasResolutionReason::MultipleConcreteService, $resolution->aliasResolutionReason());
        self::assertNull($resolution->aliasDefinition());
    }

    public function testPassAbstractServiceDefinitionWithMultipleConcreteDefinitionWithPrimaryReturnsCorrectResolution() : void {
        $subject = new StandardAliasDefinitionResolver();
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $concrete1 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->bazImplementation())
            ->build();
        $concrete2 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation(), true)
            ->build();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($serviceDefinition)
            ->withServiceDefinition($concrete1)
            ->withServiceDefinition($concrete2)
            ->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($serviceDefinition->type())
                    ->withConcrete($concrete1->type())
                    ->build()
            )->withAliasDefinition(
                $aliasDefinition = AliasDefinitionBuilder::forAbstract($serviceDefinition->type())
                    ->withConcrete($concrete2->type())
                    ->build()
            )->build();

        $resolution = $subject->resolveAlias($containerDefinition, $serviceDefinition->type());

        self::assertSame(AliasResolutionReason::ConcreteServiceIsPrimary, $resolution->aliasResolutionReason());
        self::assertSame($aliasDefinition, $resolution->aliasDefinition());
    }

    public function testDelegatedAbstractServiceHasNoAlias() : void {
        $subject = new StandardAliasDefinitionResolver();

        $abstract = ServiceDefinitionBuilder::forAbstract(
            Fixtures::delegatedService()->serviceInterface()
        )->build();
        $concrete = ServiceDefinitionBuilder::forConcrete(
            Fixtures::delegatedService()->fooService()
        )->build();
        $alias = AliasDefinitionBuilder::forAbstract($abstract->type())->withConcrete($concrete->type())->build();
        $delegate = ServiceDelegateDefinitionBuilder::forService($abstract->type())
            ->withDelegateMethod(
                Fixtures::delegatedService()->serviceFactory(),
                'createService'
            )->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($abstract)
            ->withServiceDefinition($concrete)
            ->withAliasDefinition($alias)
            ->withServiceDelegateDefinition($delegate)
            ->build();

        $resolution = $subject->resolveAlias($containerDefinition, $abstract->type());

        self::assertNull($resolution->aliasDefinition());
        self::assertSame(AliasResolutionReason::ServiceIsDelegated, $resolution->aliasResolutionReason());
    }

    public function testMultiplePrimaryServiceIsNull() : void {
        $subject = new StandardAliasDefinitionResolver();

        $abstract = ServiceDefinitionBuilder::forAbstract(
            Fixtures::ambiguousAliasedServices()->fooInterface()
        )->build();
        $one = ServiceDefinitionBuilder::forConcrete(
            Fixtures::ambiguousAliasedServices()->barImplementation(),
            true
        )->build();
        $two = ServiceDefinitionBuilder::forConcrete(
            Fixtures::ambiguousAliasedServices()->bazImplementation(),
            true
        )->build();
        $oneAlias = AliasDefinitionBuilder::forAbstract($abstract->type())
            ->withConcrete($one->type())
            ->build();
        $twoAlias = AliasDefinitionBuilder::forAbstract($abstract->type())
            ->withConcrete($two->type())
            ->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($abstract)
            ->withServiceDefinition($one)
            ->withServiceDefinition($two)
            ->withAliasDefinition($oneAlias)
            ->withAliasDefinition($twoAlias)
            ->build();

        $resolution = $subject->resolveAlias($containerDefinition, $abstract->type());

        self::assertNull($resolution->aliasDefinition());
        self::assertSame(AliasResolutionReason::MultiplePrimaryService, $resolution->aliasResolutionReason());
    }
}
