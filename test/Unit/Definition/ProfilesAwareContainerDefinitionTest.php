<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Definition;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Unit\Helper\HasMockDefinitions;
use PHPUnit\Framework\TestCase;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ProfilesAwareContainerDefinitionTest extends TestCase {

    use HasMockDefinitions;

    public function testGetServiceDefinitionsOnlyReturnThoseMatchingProfiles() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $fooImplementation = $this->concreteServiceDefinition(Fixtures::singleConcreteService()->fooImplementation(), profiles: ['foo']),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->quxImplementation(), profiles: ['default', 'bar', 'baz']),
                $fooInterface = $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), profiles: ['foo', 'qux', 'test']),
            ]
        );

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['foo']));

        self::assertSame([$fooImplementation, $fooInterface], $subject->serviceDefinitions());
    }

    public function testGetAliasDefinitionsDoNotIncludeAliasWithInvalidAbstractProfiles() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), profiles: ['bar']),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->barImplementation()),
            ],
            aliasDefinitions: [
                $this->aliasDefinition(
                    Fixtures::ambiguousAliasedServices()->fooInterface(),
                    Fixtures::ambiguousAliasedServices()->barImplementation()
                )
            ],
        );

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::assertCount(0, $subject->aliasDefinitions());
    }


    public function testGetAliasDefinitionsDoNotIncludeAliasWithInvalidConcreteProfiles() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface()),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->barImplementation(), profiles: ['foo']),
            ],
            aliasDefinitions: [
                $this->aliasDefinition(
                    Fixtures::ambiguousAliasedServices()->fooInterface(),
                    Fixtures::ambiguousAliasedServices()->barImplementation(),
                ),
            ],
        );

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::assertCount(0, $subject->aliasDefinitions());
    }

    public function testGetAliasDefinitionsIncludeCorrectProfiles() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface()),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->barImplementation()),
            ],
            aliasDefinitions: [
                $this->aliasDefinition(
                    Fixtures::ambiguousAliasedServices()->fooInterface(),
                    Fixtures::ambiguousAliasedServices()->barImplementation(),
                ),
            ],
        );

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::assertCount(1, $subject->aliasDefinitions());
    }

    public function testGetAliasDefinitionAbstractNotServiceDefinitionThrowsException() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->barImplementation()),
            ],
            aliasDefinitions: [
                $this->aliasDefinition(
                    Fixtures::ambiguousAliasedServices()->fooInterface(),
                    Fixtures::ambiguousAliasedServices()->barImplementation(),
                ),
            ],
        );

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::expectException(InvalidAlias::class);
        self::expectExceptionMessage(sprintf(
            'An AliasDefinition has an abstract type, %s, that is not a registered ServiceDefinition.',
            Fixtures::ambiguousAliasedServices()->fooInterface()->name()
        ));

        $subject->aliasDefinitions();
    }

    public function testGetAliasDefinitionConcreteNotServiceDefinitionThrowsException() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface()),
            ],
            aliasDefinitions: [
                $this->aliasDefinition(
                    Fixtures::ambiguousAliasedServices()->fooInterface(),
                    Fixtures::ambiguousAliasedServices()->barImplementation()
                ),
            ],
        );

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['default']));

        self::expectException(InvalidAlias::class);
        self::expectExceptionMessage(sprintf(
            'An AliasDefinition has a concrete type, %s, that is not a registered ServiceDefinition.',
            Fixtures::ambiguousAliasedServices()->barImplementation()->name()
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

    public function testGetServiceDelegateReturnsOnlyThoseWithActiveProfiles() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDelegateDefinitions: [
                $this->serviceDelegateDefinition(
                    Fixtures::injectConstructorServices()->injectStringService(),
                    Fixtures::injectConstructorServices()->injectStringService(),
                    'create',
                    ['appletree']
                ),
                $one = $this->serviceDelegateDefinition(
                    Fixtures::injectConstructorServices()->injectIntService(),
                    Fixtures::injectConstructorServices()->injectIntService(),
                    'create',
                    ['not', 'like', 'us']
                ),
                $two = $this->serviceDelegateDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    'create',
                    ['default', 'us']
                )
            ]
        );

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['us']));

        self::assertSame([$one, $two], $subject->serviceDelegateDefinitions());
    }

    public function testGetInjectDefinitionsRespectProfiles() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->concreteServiceDefinition(Fixtures::injectConstructorServices()->injectProfilesStringService()),
            ],
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectProfilesStringService(),
                    '__construct',
                    'val',
                    types()->string(),
                    'a string',
                    profiles: ['test'],
                ),
                $injectDefinition = $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectProfilesStringService(),
                    '__construct',
                    'val',
                    types()->string(),
                    'a different string',
                    profiles: ['prod'],
                ),
            ]
        );

        $subject = new ProfilesAwareContainerDefinition($containerDefinition, Profiles::fromList(['prod']));

        $expected = [$injectDefinition];
        self::assertSame($expected, $subject->injectDefinitions());
    }
}
