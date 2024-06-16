<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Definition;

use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\stringType;

class ProfilesAwareContainerDefinitionTest extends TestCase {

    public function testGetServiceDefinitionsOnlyReturnThoseMatchingProfiles() : void {
        $serviceDefinition1 = ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
            ->withProfiles(['foo'])
            ->build();
        $serviceDefinition2 = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->quxImplementation())
            ->withProfiles(['default', 'bar', 'baz'])
            ->build();
        $serviceDefinition3 = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->withProfiles(['foo', 'qux', 'test'])
            ->build();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($serviceDefinition1)
            ->withServiceDefinition($serviceDefinition2)
            ->withServiceDefinition($serviceDefinition3)
            ->build();

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['foo']));

        self::assertSame([$serviceDefinition1, $serviceDefinition3], $subject->serviceDefinitions());
    }

    public function testGetAliasDefinitionsDoNotIncludeAliasWithInvalidAbstractProfiles() : void {
        $abstract = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->withProfiles(['bar'])
            ->build();
        $concrete = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())
            ->build();
        $alias = AliasDefinitionBuilder::forAbstract($abstract->type())->withConcrete($concrete->type())->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($abstract)
            ->withServiceDefinition($concrete)
            ->withAliasDefinition($alias)
            ->build();

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::assertCount(0, $subject->aliasDefinitions());
    }


    public function testGetAliasDefinitionsDoNotIncludeAliasWithInvalidConcreteProfiles() : void {
        $abstract = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $concrete = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())
            ->withProfiles(['foo'])
            ->build();
        $alias = AliasDefinitionBuilder::forAbstract($abstract->type())->withConcrete($concrete->type())->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($abstract)
            ->withServiceDefinition($concrete)
            ->withAliasDefinition($alias)
            ->build();

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::assertCount(0, $subject->aliasDefinitions());
    }

    public function testGetAliasDefinitionsIncludeCorrectProfiles() : void {
        $abstract = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $concrete = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())
            ->build();
        $alias = AliasDefinitionBuilder::forAbstract($abstract->type())->withConcrete($concrete->type())->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($abstract)
            ->withServiceDefinition($concrete)
            ->withAliasDefinition($alias)
            ->build();

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::assertCount(1, $subject->aliasDefinitions());
    }

    public function testGetAliasDefinitionAbstractNotServiceDefinitionThrowsException() : void {
        $concrete = ServiceDefinitionBuilder::forConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())
            ->build();
        $alias = AliasDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->withConcrete($concrete->type())
            ->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($concrete)
            ->withAliasDefinition($alias)
            ->build();

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::expectException(InvalidAlias::class);
        self::expectExceptionMessage(sprintf(
            'An AliasDefinition has an abstract type, %s, that is not a registered ServiceDefinition.',
            Fixtures::ambiguousAliasedServices()->fooInterface()->getName()
        ));

        $subject->aliasDefinitions();
    }

    public function testGetAliasDefinitionConcreteNotServiceDefinitionThrowsException() : void {
        $abstract = ServiceDefinitionBuilder::forAbstract(Fixtures::ambiguousAliasedServices()->fooInterface())
            ->build();
        $alias = AliasDefinitionBuilder::forAbstract($abstract->type())
            ->withConcrete(Fixtures::ambiguousAliasedServices()->barImplementation())
            ->build();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($abstract)
            ->withAliasDefinition($alias)
            ->build();

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::expectException(InvalidAlias::class);
        self::expectExceptionMessage(sprintf(
            'An AliasDefinition has a concrete type, %s, that is not a registered ServiceDefinition.',
            Fixtures::ambiguousAliasedServices()->barImplementation()->getName()
        ));

        $subject->aliasDefinitions();
    }

    public function testGetServicePrepareDefinitionsDelegatesToInjectedContainerDefinition() : void {
        $containerDefinition = $this->getMockBuilder(ContainerDefinition::class)->getMock();
        $containerDefinition->expects($this->once())
            ->method('servicePrepareDefinitions')
            ->willReturn([]);

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::assertSame([], $subject->servicePrepareDefinitions());
    }

    public function testGetServiceDelegateDefinitionsDelegatesToInjectedContainerDefinition() : void {
        $containerDefinition = $this->getMockBuilder(ContainerDefinition::class)->getMock();
        $containerDefinition->expects($this->once())
            ->method('serviceDelegateDefinitions')
            ->willReturn([]);

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::assertSame([], $subject->serviceDelegateDefinitions());
    }

    public function testGetInjectDefinitionsRespectProfiles() : void {
        $service = ServiceDefinitionBuilder::forConcrete(Fixtures::injectConstructorServices()->injectProfilesStringService())
            ->build();
        $injectDefinition1 = InjectDefinitionBuilder::forService($service->type())
            ->withMethod('__construct', stringType(), 'val')
            ->withValue('a string')
            ->withProfiles('test')
            ->build();
        $injectDefinition2 = InjectDefinitionBuilder::forService($service->type())
            ->withMethod('__construct', stringType(), 'val')
            ->withValue('a different string')
            ->withProfiles('prod')
            ->build();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition($service)
            ->withInjectDefinition($injectDefinition1)
            ->withInjectDefinition($injectDefinition2)
            ->build();

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['prod']));

        $expected = [$injectDefinition2];
        self::assertSame($expected, $subject->injectDefinitions());
    }
}
