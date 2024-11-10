<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactory;

use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasResolutionReason;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\StandardAliasDefinitionResolver;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Unit\Helper\HasMockDefinitions;
use PHPUnit\Framework\TestCase;

final class StandardAliasDefinitionResolverTest extends TestCase {

    use HasMockDefinitions;

    public function testPassAbstractServiceDefinitionWithNoConcreteDefinitionReturnsCorrectResolution() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface())
            ]
        );

        $subject = new StandardAliasDefinitionResolver();
        $resolution = $subject->resolveAlias($containerDefinition, Fixtures::ambiguousAliasedServices()->fooInterface());

        self::assertSame(AliasResolutionReason::NoConcreteService, $resolution->aliasResolutionReason());
        self::assertNull($resolution->aliasDefinition());
    }

    public function testPassAbstractServiceDefinitionWithSingleConcreteDefinitionReturnsCorrectResolution() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface()),
                $this->concreteServiceDefinition(Fixtures::singleConcreteService()->fooImplementation()),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->barImplementation()),
            ],
            aliasDefinitions: [
                $aliasDefinition = $this->aliasDefinition(
                    Fixtures::ambiguousAliasedServices()->fooInterface(),
                    Fixtures::ambiguousAliasedServices()->barImplementation()
                )
            ]
        );

        $subject = new StandardAliasDefinitionResolver();
        $resolution = $subject->resolveAlias($containerDefinition, Fixtures::ambiguousAliasedServices()->fooInterface());

        self::assertSame(AliasResolutionReason::SingleConcreteService, $resolution->aliasResolutionReason());
        self::assertSame($aliasDefinition, $resolution->aliasDefinition());
    }

    public function testPassAbstractServiceDefinitionWithMultipleConcreteDefinitionReturnsCorrectResolution() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface()),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->bazImplementation()),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->barImplementation()),
            ],
            aliasDefinitions: [
                $this->aliasDefinition(
                    Fixtures::ambiguousAliasedServices()->fooInterface(),
                    Fixtures::ambiguousAliasedServices()->bazImplementation()
                ),
                $this->aliasDefinition(
                    Fixtures::ambiguousAliasedServices()->fooInterface(),
                    Fixtures::ambiguousAliasedServices()->barImplementation()
                )
            ]
        );

        $subject = new StandardAliasDefinitionResolver();
        $resolution = $subject->resolveAlias($containerDefinition, Fixtures::ambiguousAliasedServices()->fooInterface());

        self::assertSame(AliasResolutionReason::MultipleConcreteService, $resolution->aliasResolutionReason());
        self::assertNull($resolution->aliasDefinition());
    }

    public function testPassAbstractServiceDefinitionWithMultipleConcreteDefinitionWithPrimaryReturnsCorrectResolution() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface()),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->bazImplementation()),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->barImplementation(), isPrimary: true),
            ],
            aliasDefinitions: [
                $this->aliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->bazImplementation()),
                $aliasDefinition = $this->aliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->barImplementation()),
            ],
        );

        $subject = new StandardAliasDefinitionResolver();
        $resolution = $subject->resolveAlias($containerDefinition, Fixtures::ambiguousAliasedServices()->fooInterface());

        self::assertSame(AliasResolutionReason::ConcreteServiceIsPrimary, $resolution->aliasResolutionReason());
        self::assertSame($aliasDefinition, $resolution->aliasDefinition());
    }

    public function testDelegatedAbstractServiceHasNoAlias() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::delegatedService()->serviceInterface()),
                $this->concreteServiceDefinition(Fixtures::delegatedService()->fooService())
            ],
            serviceDelegateDefinitions: [
                $this->serviceDelegateDefinition(
                    Fixtures::delegatedService()->serviceInterface(),
                    Fixtures::delegatedService()->serviceFactory(),
                    'createService',
                )
            ],
            aliasDefinitions: [
                $this->aliasDefinition(Fixtures::delegatedService()->serviceInterface(), Fixtures::delegatedService()->fooService())
            ]
        );

        $subject = new StandardAliasDefinitionResolver();
        $resolution = $subject->resolveAlias($containerDefinition, Fixtures::delegatedService()->serviceInterface());

        self::assertSame(AliasResolutionReason::ServiceIsDelegated, $resolution->aliasResolutionReason());
        self::assertNull($resolution->aliasDefinition());
    }

    public function testMultiplePrimaryServiceIsNull() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->abstractServiceDefinition(Fixtures::ambiguousAliasedServices()->fooInterface()),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->barImplementation(), isPrimary: true),
                $this->concreteServiceDefinition(Fixtures::ambiguousAliasedServices()->bazImplementation(), isPrimary: true),
            ],
            aliasDefinitions: [
                $this->aliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->barImplementation()),
                $this->aliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->bazImplementation())
            ]
        );

        $subject = new StandardAliasDefinitionResolver();
        $resolution = $subject->resolveAlias($containerDefinition, Fixtures::ambiguousAliasedServices()->fooInterface());

        self::assertSame(AliasResolutionReason::MultiplePrimaryService, $resolution->aliasResolutionReason());
        self::assertNull($resolution->aliasDefinition());
    }
}
