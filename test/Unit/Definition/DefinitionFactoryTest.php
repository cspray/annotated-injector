<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Definition;

use Closure;
use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Definition\DefinitionFactory;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\InjectAttributeRequired;
use Cspray\AnnotatedContainer\Exception\ServiceAttributeRequired;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateAttributeRequired;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateReturnsIntersectionType;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateReturnsScalarType;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateReturnsUnionType;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateReturnsUnknownType;
use Cspray\AnnotatedContainer\Exception\ServicePrepareAttributeRequired;
use Cspray\AnnotatedContainer\Exception\WrongTargetForInjectAttribute;
use Cspray\AnnotatedContainer\Exception\WrongTargetForServiceAttribute;
use Cspray\AnnotatedContainer\Exception\WrongTargetForServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Exception\WrongTargetForServicePrepareAttribute;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;
use Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ImplicitServiceDelegateScalarType;
use Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ImplicitServiceDelegateIntersectionType;
use Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ImplicitServiceDelegateUnionType;
use Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ImplicitServiceDelegateNoType;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareInterface;
use function Cspray\AnnotatedContainer\Reflection\types;

final class DefinitionFactoryTest extends TestCase {

    private DefinitionFactory $subject;

    protected function setUp() : void {
        $this->subject = new DefinitionFactory();
    }

    public function testServiceDefinitionFromAnnotatedTargetWithWrongAttributeThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServicePrepareAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);

        $this->expectException(ServiceAttributeRequired::class);
        $this->expectExceptionMessage(
            'The AnnotatedTarget::attributeInstance MUST return a type of ' . ServiceAttribute::class . ' but ' .
             $attribute::class . ' was provided.'
        );

        $this->subject->serviceDefinitionFromAnnotatedTarget($target);
    }

    public function testServiceDefinitionFromAnnotatedTargetWithWrongTargetReflectionThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServiceAttribute::class);

        $target->expects($this->once())
            ->method('attributeInstance')
            ->willReturn($attribute);

        $target->expects($this->once())
            ->method('targetReflection')
            ->willReturn(new \ReflectionMethod(
                Fixtures::singleConcreteService()->fooImplementation()->name(),
                'postConstruct'
            ));

        $this->expectException(WrongTargetForServiceAttribute::class);
        $this->expectExceptionMessage(
            'The AnnotatedTarget::targetReflection MUST return an instance of ReflectionClass but ReflectionMethod was provided.'
        );

        $this->subject->serviceDefinitionFromAnnotatedTarget($target);
    }

    public static function singleConcreteServiceCreatorProvider() : array {
        return [
            'annotatedTarget' => [
                function(ServiceAttribute $attribute) : ServiceDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);
                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        new \ReflectionClass(
                            Fixtures::singleConcreteService()->fooImplementation()->name()
                        )
                    );

                    return $this->subject->serviceDefinitionFromAnnotatedTarget($target);
                }
            ],
            'objectType' => [
                fn(ServiceAttribute $attribute) : ServiceDefinition =>
                    $this->subject->serviceDefinitionFromObjectTypeAndAttribute(
                        Fixtures::singleConcreteService()->fooImplementation(),
                        $attribute
                    )
            ],
            'manualSetup' => [
                fn(ServiceAttribute $attribute) : ServiceDefinition =>
                    $this->subject->serviceDefinitionFromManualSetup(
                        Fixtures::singleConcreteService()->fooImplementation(),
                        $attribute,
                        true
                    )
            ]
        ];
    }

    public static function interfaceAbstractServiceCreatorProvider() : array {
        return [
            'annotatedTarget' => [
                function(ServiceAttribute $attribute) : ServiceDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);
                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        new \ReflectionClass(
                            Fixtures::implicitAliasedServices()->fooInterface()->name()
                        )
                    );

                    return $this->subject->serviceDefinitionFromAnnotatedTarget($target);
                }
            ],
            'objectType' => [
                fn(ServiceAttribute $attribute) : ServiceDefinition =>
                    $this->subject->serviceDefinitionFromObjectTypeAndAttribute(
                        Fixtures::implicitAliasedServices()->fooInterface(),
                        $attribute
                    )
            ],
            'manualSetup' => [
                fn(ServiceAttribute $attribute) : ServiceDefinition =>
                    $this->subject->serviceDefinitionFromManualSetup(
                        Fixtures::implicitAliasedServices()->fooInterface(),
                        $attribute,
                        false
                    )
            ]
        ];
    }

    public static function abstractClassAbstractServiceCreatorProvider() : array {
        return [
            'annotatedTarget' => [
                function(ServiceAttribute $attribute) : ServiceDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);
                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        new \ReflectionClass(
                            Fixtures::abstractClassAliasedService()->fooAbstract()->name()
                        )
                    );

                    return $this->subject->serviceDefinitionFromAnnotatedTarget($target);
                }
            ],
            'objectType' => [
                fn(ServiceAttribute $attribute) =>
                    $this->subject->serviceDefinitionFromObjectTypeAndAttribute(
                        Fixtures::abstractClassAliasedService()->fooAbstract(),
                        $attribute
                    )
            ],
            'manualSetup' => [
                fn(ServiceAttribute $attribute) =>
                    $this->subject->serviceDefinitionFromManualSetup(
                        Fixtures::abstractClassAliasedService()->fooAbstract(),
                        $attribute,
                        false
                    )
            ]
        ];
    }

    #[DataProvider('singleConcreteServiceCreatorProvider')]
    /**
     * @param Closure(ServiceAttribute):ServiceDefinition $definitionCreator
     */
    public function testServiceDefinitionWithMinimallyValidAttributeCreatesCorrectDefinition(
        Closure $definitionCreator
    ) : void {
        $attribute = $this->createMock(ServiceAttribute::class);
        $attribute->expects($this->once())->method('isPrimary')->willReturn(false);
        $attribute->expects($this->once())->method('name')->willReturn(null);
        $attribute->expects($this->once())->method('profiles')->willReturn([]);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame(
            Fixtures::singleConcreteService()->fooImplementation(),
            $definition->type()
        );
        self::assertNull($definition->name());
        self::assertFalse($definition->isPrimary());
        self::assertSame(['default'], $definition->profiles());
        self::assertTrue($definition->isConcrete());
        self::assertFalse($definition->isAbstract());
        self::assertSame($attribute, $definition->attribute());
    }

    #[DataProvider('singleConcreteServiceCreatorProvider')]
    /**
     * @param Closure(ServiceAttribute):ServiceDefinition $definitionCreator
     */
    public function testServiceDefinitionWithAttributeDefinedProfiles(
        Closure $definitionCreator
    ) : void {
        $attribute = $this->createMock(ServiceAttribute::class);

        $attribute->expects($this->once())->method('isPrimary')->willReturn(false);
        $attribute->expects($this->once())->method('name')->willReturn(null);
        $attribute->expects($this->once())->method('profiles')->willReturn(['foo', 'bar']);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame(
            Fixtures::singleConcreteService()->fooImplementation(),
            $definition->type()
        );
        self::assertNull($definition->name());
        self::assertFalse($definition->isPrimary());
        self::assertSame(['foo', 'bar'], $definition->profiles());
        self::assertTrue($definition->isConcrete());
        self::assertFalse($definition->isAbstract());
        self::assertSame($attribute, $definition->attribute());
    }

    #[DataProvider('singleConcreteServiceCreatorProvider')]
    /**
     * @param Closure(ServiceAttribute):ServiceDefinition $definitionCreator
     */
    public function testServiceDefinitionWithAttributeDefinedName(
        Closure $definitionCreator
    ) : void {
        $attribute = $this->createMock(ServiceAttribute::class);
        $attribute->expects($this->once())->method('isPrimary')->willReturn(false);
        $attribute->expects($this->once())->method('name')->willReturn('serviceName');
        $attribute->expects($this->once())->method('profiles')->willReturn([]);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame(
            Fixtures::singleConcreteService()->fooImplementation(),
            $definition->type()
        );
        self::assertSame('serviceName', $definition->name());
        self::assertFalse($definition->isPrimary());
        self::assertSame(['default'], $definition->profiles());
        self::assertTrue($definition->isConcrete());
        self::assertFalse($definition->isAbstract());
        self::assertSame($attribute, $definition->attribute());
    }

    #[DataProvider('singleConcreteServiceCreatorProvider')]
    /**
     * @param Closure(ServiceAttribute):ServiceDefinition $definitionCreator
     */
    public function testServiceDefinitionFromAnnotatedTargetWithAttributeDefinedIsPrimary(
        Closure $definitionCreator
    ) : void {
        $attribute = $this->createMock(ServiceAttribute::class);
        $attribute->expects($this->once())->method('isPrimary')->willReturn(true);
        $attribute->expects($this->once())->method('name')->willReturn('serviceName');
        $attribute->expects($this->once())->method('profiles')->willReturn([]);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame(
            Fixtures::singleConcreteService()->fooImplementation(),
            $definition->type()
        );
        self::assertSame('serviceName', $definition->name());
        self::assertTrue($definition->isPrimary());
        self::assertSame(['default'], $definition->profiles());
        self::assertTrue($definition->isConcrete());
        self::assertFalse($definition->isAbstract());
        self::assertSame($attribute, $definition->attribute());
    }

    #[DataProvider('interfaceAbstractServiceCreatorProvider')]
    /**
     * @param Closure(ServiceAttribute):ServiceDefinition $definitionCreator
     */
    public function testServiceDefinitionIsInterfaceMarkedAsAbstract(
        Closure $definitionCreator
    ) : void {
        $attribute = $this->createMock(ServiceAttribute::class);
        $attribute->expects($this->once())->method('isPrimary')->willReturn(false);
        $attribute->expects($this->once())->method('name')->willReturn('serviceName');
        $attribute->expects($this->once())->method('profiles')->willReturn([]);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame(
            Fixtures::implicitAliasedServices()->fooInterface(),
            $definition->type()
        );
        self::assertSame('serviceName', $definition->name());
        self::assertFalse($definition->isPrimary());
        self::assertSame(['default'], $definition->profiles());
        self::assertFalse($definition->isConcrete());
        self::assertTrue($definition->isAbstract());
        self::assertSame($attribute, $definition->attribute());
    }

    #[DataProvider('abstractClassAbstractServiceCreatorProvider')]
    /**
     * @param Closure(ServiceAttribute):ServiceDefinition $definitionCreator
     */
    public function testServiceDefinitionFromAnnotatedTargetWithAttributeDefinedIsAbstractClassMarkedAsAbstract(
        Closure $definitionCreator
    ) : void {
        $attribute = $this->createMock(ServiceAttribute::class);
        $attribute->expects($this->once())->method('isPrimary')->willReturn(false);
        $attribute->expects($this->once())->method('name')->willReturn('serviceName');
        $attribute->expects($this->once())->method('profiles')->willReturn([]);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame(
            Fixtures::abstractClassAliasedService()->fooAbstract(),
            $definition->type()
        );
        self::assertSame('serviceName', $definition->name());
        self::assertFalse($definition->isPrimary());
        self::assertSame(['default'], $definition->profiles());
        self::assertFalse($definition->isConcrete());
        self::assertTrue($definition->isAbstract());
        self::assertSame($attribute, $definition->attribute());
    }

    public function testServicePrepareDefinitionFromAnnotatedTargetWithWrongAttributeThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServiceAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);

        $this->expectException(ServicePrepareAttributeRequired::class);
        $this->expectExceptionMessage(
            'The AnnotatedTarget::attributeInstance MUST return a type of ' . ServicePrepareAttribute::class . ' but ' .
            $attribute::class . ' was provided.'
        );

        $this->subject->servicePrepareDefinitionFromAnnotatedTarget($target);
    }

    public function testServicePrepareDefinitionFromAnnotatedTargetWithWrongTargetReflectionThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServicePrepareAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
        $target->expects($this->once())->method('targetReflection')->willReturn(
            new \ReflectionClass(Fixtures::singleConcreteService()->fooImplementation()->name())
        );

        $this->expectException(WrongTargetForServicePrepareAttribute::class);
        $this->expectExceptionMessage(
            'The AnnotatedTarget::targetReflection MUST return an instance of ReflectionMethod but ReflectionClass was provided.'
        );

        $this->subject->servicePrepareDefinitionFromAnnotatedTarget($target);
    }

    public static function servicePrepareDefinitionCreatorProvider() : array {
        return [
            'annotatedTarget' => [
                function(ServicePrepareAttribute $servicePrepareAttribute) : ServicePrepareDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);
                    $target->expects($this->once())->method('attributeInstance')->willReturn($servicePrepareAttribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        new \ReflectionMethod(LoggerAwareInterface::class, 'setLogger')
                    );

                    return $this->subject->servicePrepareDefinitionFromAnnotatedTarget($target);
                }
            ],
            'classMethod' => [
                fn(ServicePrepareAttribute $servicePrepareAttribute) =>
                    $this->subject->servicePrepareDefinitionFromClassMethodAndAttribute(
                        types()->class(LoggerAwareInterface::class),
                        'setLogger',
                        $servicePrepareAttribute,
                    )
            ]
        ];
    }

    #[DataProvider('servicePrepareDefinitionCreatorProvider')]
    /**
     * @param Closure(ServicePrepareAttribute):ServicePrepareDefinition $definitionCreator
     */
    public function testServicePrepareDefinitionFromAnnotatedTargetWithValidParametersReturnsCorrectDefinition(
        Closure $definitionCreator
    ) : void {
        $attribute = $this->createMock(ServicePrepareAttribute::class);
        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame(
            types()->class(LoggerAwareInterface::class),
            $definition->service()
        );
        self::assertSame(
            'setLogger',
            $definition->methodName()
        );
        self::assertSame($attribute, $definition->attribute());
    }

    public function testServiceDelegateDefinitionFromAnnotatedTargetWithWrongAttributeThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServiceAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);

        $this->expectException(ServiceDelegateAttributeRequired::class);
        $this->expectExceptionMessage(sprintf(
            'The AnnotatedTarget::attributeInstance MUST return a type of %s but %s was provided.',
            ServiceDelegateAttribute::class,
            $attribute::class
        ));

        $this->subject->serviceDelegateDefinitionFromAnnotatedTarget($target);
    }

    public function testServiceDelegateDefinitionFromAnnotatedTargetWithWrongTargetReflectionThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServiceDelegateAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
        $target->expects($this->once())->method('targetReflection')->willReturn(
            new \ReflectionClass(Fixtures::singleConcreteService()->fooImplementation()->name())
        );

        $this->expectException(WrongTargetForServiceDelegateAttribute::class);
        $this->expectExceptionMessage(
            'The AnnotatedTarget::targetReflection MUST return an instance of ReflectionMethod but ReflectionClass was provided.'
        );

        $this->subject->serviceDelegateDefinitionFromAnnotatedTarget($target);
    }

    public function testServiceDelegateDefinitionFromAnnotatedTargetWithNonObjectReturnTypeThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServiceDelegateAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
        $target->expects($this->once())->method('targetReflection')->willReturn(
            new \ReflectionMethod(
                ImplicitServiceDelegateScalarType\FooFactory::class,
                'create'
            )
        );

        $this->expectException(ServiceDelegateReturnsScalarType::class);
        $this->expectExceptionMessage(sprintf(
            'The ServiceDelegate %s::create returns a scalar type. All ServiceDelegates MUST return an object type.',
            ImplicitServiceDelegateScalarType\FooFactory::class
        ));

        $this->subject->serviceDelegateDefinitionFromAnnotatedTarget($target);
    }

    public function testServiceDelegateDefinitionFromAnnotatedTargetWithIntersectionReturnTypeThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServiceDelegateAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
        $target->expects($this->once())->method('targetReflection')->willReturn(
            new \ReflectionMethod(
                ImplicitServiceDelegateIntersectionType\FooFactory::class,
                'create'
            )
        );

        $this->expectException(ServiceDelegateReturnsIntersectionType::class);
        $this->expectExceptionMessage(sprintf(
            'The ServiceDelegate %s::create returns an intersection type. At this time intersection types are not supported.',
            ImplicitServiceDelegateIntersectionType\FooFactory::class
        ));

        $this->subject->serviceDelegateDefinitionFromAnnotatedTarget($target);
    }

    public function testServiceDelegateDefinitionFromAnnotatedTargetWithUnionReturnTypeThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServiceDelegateAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
        $target->expects($this->once())->method('targetReflection')->willReturn(
            new \ReflectionMethod(
                ImplicitServiceDelegateUnionType\FooFactory::class,
                'create'
            )
        );

        $this->expectException(ServiceDelegateReturnsUnionType::class);
        $this->expectExceptionMessage(sprintf(
            'The ServiceDelegate %s::create returns a union type. At this time union types are not supported.',
            ImplicitServiceDelegateUnionType\FooFactory::class
        ));

        $this->subject->serviceDelegateDefinitionFromAnnotatedTarget($target);
    }

    public function testServiceDelegateDefinitionFromAnnotatedTargetWithNullReturnTypeThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServiceDelegateAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
        $target->expects($this->once())->method('targetReflection')->willReturn(
            new \ReflectionMethod(
                ImplicitServiceDelegateNoType\FooFactory::class,
                'create'
            )
        );

        $this->expectException(ServiceDelegateReturnsUnknownType::class);
        $this->expectExceptionMessage(sprintf(
            'The ServiceDelegate %s::create does not have a return type. A ServiceDelegate MUST declare an object return type.',
            ImplicitServiceDelegateNoType\FooFactory::class,
        ));

        $this->subject->serviceDelegateDefinitionFromAnnotatedTarget($target);
    }

    public function testServiceDelegateDefinitionFromClassMethodAndAttributeWithNonObjectReturnTypeThrowsException() : void {
        $attribute = $this->createMock(ServiceDelegateAttribute::class);

        $this->expectException(ServiceDelegateReturnsScalarType::class);
        $this->expectExceptionMessage(sprintf(
            'The ServiceDelegate %s::create returns a scalar type. All ServiceDelegates MUST return an object type.',
            ImplicitServiceDelegateScalarType\FooFactory::class
        ));

        $this->subject->serviceDelegateDefinitionFromClassMethodAndAttribute(
            types()->class(ImplicitServiceDelegateScalarType\FooFactory::class),
            'create',
            $attribute
        );
    }

    public function testServiceDelegateDefinitionFromClassMethodAndAttributeWithIntersectionReturnTypeThrowsException() : void {
        $attribute = $this->createMock(ServiceDelegateAttribute::class);

        $this->expectException(ServiceDelegateReturnsIntersectionType::class);
        $this->expectExceptionMessage(sprintf(
            'The ServiceDelegate %s::create returns an intersection type. At this time intersection types are not supported.',
            ImplicitServiceDelegateIntersectionType\FooFactory::class
        ));

        $this->subject->serviceDelegateDefinitionFromClassMethodAndAttribute(
            types()->class(ImplicitServiceDelegateIntersectionType\FooFactory::class),
            'create',
            $attribute
        );
    }

    public function testServiceDelegateDefinitionFromClassMethodAndAttributeWithUnionReturnTypeThrowsException() : void {
        $attribute = $this->createMock(ServiceDelegateAttribute::class);

        $this->expectException(ServiceDelegateReturnsUnionType::class);
        $this->expectExceptionMessage(sprintf(
            'The ServiceDelegate %s::create returns a union type. At this time union types are not supported.',
            ImplicitServiceDelegateUnionType\FooFactory::class
        ));

        $this->subject->serviceDelegateDefinitionFromClassMethodAndAttribute(
            types()->class(ImplicitServiceDelegateUnionType\FooFactory::class),
            'create',
            $attribute
        );
    }

    public function testServiceDelegateDefinitionFromClassMethodAndAttributeWithNullReturnTypeThrowsException() : void {
        $attribute = $this->createMock(ServiceDelegateAttribute::class);

        $this->expectException(ServiceDelegateReturnsUnknownType::class);
        $this->expectExceptionMessage(sprintf(
            'The ServiceDelegate %s::create does not have a return type. A ServiceDelegate MUST declare an object return type.',
            ImplicitServiceDelegateNoType\FooFactory::class,
        ));

        $this->subject->serviceDelegateDefinitionFromClassMethodAndAttribute(
            types()->class(ImplicitServiceDelegateNoType\FooFactory::class),
            'create',
            $attribute
        );
    }

    public static function serviceDelegateDefinitionCreatorProvider() : array {
        return [
            'annotatedTarget' => [
                function(ServiceDelegateAttribute $attribute) : ServiceDelegateDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);
                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        new \ReflectionMethod(
                            Fixtures::delegatedService()->serviceFactory()->name(),
                            'createService'
                        )
                    );

                    return $this->subject->serviceDelegateDefinitionFromAnnotatedTarget($target);
                }
            ],
            'classMethod' => [
               fn(ServiceDelegateAttribute $attribute) : ServiceDelegateDefinition =>
                   $this->subject->serviceDelegateDefinitionFromClassMethodAndAttribute(
                       Fixtures::delegatedService()->serviceFactory(),
                       'createService',
                       $attribute
                   )
            ],
        ];
    }

    #[DataProvider('serviceDelegateDefinitionCreatorProvider')]
    /**
     * @param Closure(ServiceDelegateAttribute):ServiceDelegateDefinition $definitionCreator
     */
    public function testServiceDelegateDefinitionFromAnnotatedTargetWithValidParametersCreatesDefinition(
        Closure $definitionCreator
    ) : void {
        $attribute = $this->createMock(ServiceDelegateAttribute::class);
        $attribute->method('profiles')->willReturn([]);
        $attribute->method('service')->willReturn(null);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame(
            Fixtures::delegatedService()->serviceInterface(),
            $definition->serviceType()
        );
        self::assertSame(
            Fixtures::delegatedService()->serviceFactory(),
            $definition->delegateType()
        );
        self::assertSame(
            'createService',
            $definition->delegateMethod(),
        );
        self::assertSame(
            ['default'],
            $definition->profiles(),
        );
        self::assertSame($attribute, $definition->attribute());
    }

    public function testServiceDelegateDefinitionFromStaticFactoryReturnsSelfCreatesObject() : void {
        $attribute = $this->createMock(ServiceDelegateAttribute::class);
        $attribute->method('profiles')->willReturn([]);
        $attribute->method('service')->willReturn(null);

        $definition = $this->subject->serviceDelegateDefinitionFromClassMethodAndAttribute(
            Fixtures::thirdPartyKitchenSink()->nonAnnotatedService(),
            'create',
            $attribute
        );

        self::assertSame(
            Fixtures::thirdPartyKitchenSink()->nonAnnotatedService(),
            $definition->serviceType()
        );
        self::assertSame(
            Fixtures::thirdPartyKitchenSink()->nonAnnotatedService(),
            $definition->delegateType()
        );
        self::assertSame(
            'create',
            $definition->delegateMethod(),
        );
        self::assertSame(
            ['default'],
            $definition->profiles(),
        );
        self::assertSame($attribute, $definition->attribute());
    }

    #[DataProvider('serviceDelegateDefinitionCreatorProvider')]
    /**
     * @param Closure(ServiceDelegateAttribute):ServiceDelegateDefinition $definitionCreator
     */
    public function testServiceDelegateWithExplicitProfilesRespected(Closure $definitionCreator) : void {
        $attribute = $this->createMock(ServiceDelegateAttribute::class);
        $attribute->method('profiles')->willReturn(['drip', 'hippopotamus', 'chameleon']);
        $attribute->method('service')->willReturn(null);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame(
            Fixtures::delegatedService()->serviceInterface(),
            $definition->serviceType()
        );
        self::assertSame(
            Fixtures::delegatedService()->serviceFactory(),
            $definition->delegateType()
        );
        self::assertSame(
            'createService',
            $definition->delegateMethod(),
        );
        self::assertSame(
            ['drip', 'hippopotamus', 'chameleon'],
            $definition->profiles(),
        );
        self::assertSame($attribute, $definition->attribute());
    }

    public function testInjectDefinitionFromAnnotatedTargetNotInjectAttributeThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(ServiceAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);

        $this->expectException(InjectAttributeRequired::class);
        $this->expectExceptionMessage(sprintf(
            'An AnnotatedTarget::attributeInstance() MUST return an instance of %s but %s was provided.',
            InjectAttribute::class,
            $attribute::class
        ));

        $this->subject->injectDefinitionFromAnnotatedTarget($target);
    }

    public function testInjectDefinitionFromAnnotatedTargetNotReflectionParameterThrowsException() : void {
        $target = $this->createMock(AnnotatedTarget::class);
        $attribute = $this->createMock(InjectAttribute::class);

        $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
        $target->expects($this->once())->method('targetReflection')->willReturn(
            new \ReflectionClass(Fixtures::injectServiceConstructorServices()->serviceInjector()->name())
        );

        $this->expectException(WrongTargetForInjectAttribute::class);
        $this->expectExceptionMessage(
            'The AnnotatedTarget::targetReflection MUST return an instance of ReflectionParameter but ReflectionClass was provided.'
        );

        $this->subject->injectDefinitionFromAnnotatedTarget($target);
    }

    public static function injectTypeProvider() : array {
        return [
            'array from AnnotatedTarget' => [
                Fixtures::injectConstructorServices()->injectArrayService(),
                ['inject', 'values'],
                types()->array(),
                'values',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectConstructorServices()->injectArrayService()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'array from ReflectionParameter' => [
                Fixtures::injectConstructorServices()->injectArrayService(),
                ['inject', 'values'],
                types()->array(),
                'values',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectConstructorServices()->injectArrayService()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'array from manual setup' => [
                Fixtures::injectConstructorServices()->injectArrayService(),
                ['inject', 'values'],
                types()->array(),
                'values',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectConstructorServices()->injectArrayService(),
                        '__construct',
                        types()->array(),
                        'values',
                        $attribute
                    );
                },
            ],
            'bool from AnnotatedTarget' => [
                Fixtures::injectConstructorServices()->injectBoolService(),
                false,
                types()->bool(),
                'flag',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectConstructorServices()->injectBoolService()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'bool from ReflectionParameter' => [
                Fixtures::injectConstructorServices()->injectBoolService(),
                false,
                types()->bool(),
                'flag',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectConstructorServices()->injectBoolService()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'bool from manual setup' => [
                Fixtures::injectConstructorServices()->injectBoolService(),
                false,
                types()->bool(),
                'flag',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectConstructorServices()->injectBoolService(),
                        '__construct',
                        types()->bool(),
                        'flag',
                        $attribute,
                    );
                }
            ],
            'explicit mixed from AnnotatedTarget' => [
                Fixtures::injectConstructorServices()->injectExplicitMixedService(),
                'whatever',
                types()->mixed(),
                'value',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectConstructorServices()->injectExplicitMixedService()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'explicit mixed from ReflectionParameter' => [
                Fixtures::injectConstructorServices()->injectExplicitMixedService(),
                'whatever',
                types()->mixed(),
                'value',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectConstructorServices()->injectExplicitMixedService()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'explicit mixed from manual setup' => [
                Fixtures::injectConstructorServices()->injectExplicitMixedService(),
                'whatever',
                types()->mixed(),
                'value',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectConstructorServices()->injectExplicitMixedService(),
                        '__construct',
                        types()->mixed(),
                        'value',
                        $attribute
                    );
                },
            ],
            'float from AnnotatedTarget' => [
                Fixtures::injectConstructorServices()->injectFloatService(),
                4.19,
                types()->float(),
                'dessert',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectConstructorServices()->injectFloatService()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'float from ReflectionParameter' => [
                Fixtures::injectConstructorServices()->injectFloatService(),
                4.19,
                types()->float(),
                'dessert',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectConstructorServices()->injectFloatService()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'float from manual setup' => [
                Fixtures::injectConstructorServices()->injectFloatService(),
                4.19,
                types()->float(),
                'dessert',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectConstructorServices()->injectFloatService(),
                        '__construct',
                        types()->float(),
                        'dessert',
                        $attribute
                    );
                },
            ],
            'implicit mixed from AnnotatedTarget' => [
                Fixtures::injectConstructorServices()->injectImplicitMixedService(),
                'boo, mixed',
                types()->mixed(),
                'val',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectConstructorServices()->injectImplicitMixedService()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'implicit mixed from ReflectionParameter' => [
                Fixtures::injectConstructorServices()->injectImplicitMixedService(),
                'boo, mixed',
                types()->mixed(),
                'val',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectConstructorServices()->injectImplicitMixedService()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'implicit mixed from manual setup' => [
                Fixtures::injectConstructorServices()->injectImplicitMixedService(),
                'boo, mixed',
                types()->mixed(),
                'val',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectConstructorServices()->injectImplicitMixedService(),
                        '__construct',
                        types()->mixed(),
                        'val',
                        $attribute
                    );
                },
            ],
            'int from AnnotatedTarget' => [
                Fixtures::injectConstructorServices()->injectIntService(),
                42,
                types()->int(),
                'meaningOfLife',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectConstructorServices()->injectIntService()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'int from ReflectionParameter' => [
                Fixtures::injectConstructorServices()->injectIntService(),
                42,
                types()->int(),
                'meaningOfLife',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectConstructorServices()->injectIntService()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'int from manual setup' => [
                Fixtures::injectConstructorServices()->injectIntService(),
                42,
                types()->int(),
                'meaningOfLife',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectConstructorServices()->injectIntService(),
                        '__construct',
                        types()->int(),
                        'meaningOfLife',
                        $attribute
                    );
                },
            ],
            'string from AnnotatedTarget' => [
                Fixtures::injectConstructorServices()->injectStringService(),
                'semantics, whatever',
                types()->string(),
                'val',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectConstructorServices()->injectStringService()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'string from ReflectionParameter' => [
                Fixtures::injectConstructorServices()->injectStringService(),
                'semantics, whatever',
                types()->string(),
                'val',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectConstructorServices()->injectStringService()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'string from manual setup' => [
                Fixtures::injectConstructorServices()->injectStringService(),
                'semantics, whatever',
                types()->string(),
                'val',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectConstructorServices()->injectStringService(),
                        '__construct',
                        types()->string(),
                        'val',
                        $attribute
                    );
                },
            ],
            'class from AnnotatedTarget' => [
                Fixtures::injectServiceConstructorServices()->serviceInjector(),
                Fixtures::injectServiceConstructorServices()->fooInterface()->name(),
                Fixtures::injectServiceConstructorServices()->fooInterface(),
                'foo',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectServiceConstructorServices()->serviceInjector()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'class from ReflectionParameter' => [
                Fixtures::injectServiceConstructorServices()->serviceInjector(),
                Fixtures::injectServiceConstructorServices()->fooInterface()->name(),
                Fixtures::injectServiceConstructorServices()->fooInterface(),
                'foo',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectServiceConstructorServices()->serviceInjector()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'class from manual setup' => [
                Fixtures::injectServiceConstructorServices()->serviceInjector(),
                Fixtures::injectServiceConstructorServices()->fooInterface()->name(),
                Fixtures::injectServiceConstructorServices()->fooInterface(),
                'foo',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectServiceConstructorServices()->serviceInjector(),
                        '__construct',
                        Fixtures::injectServiceConstructorServices()->fooInterface(),
                        'foo',
                        $attribute
                    );
                },
            ],
            'union from AnnotatedTarget' => [
                Fixtures::injectUnionCustomStoreServices()->unionInjector(),
                'foo',
                types()->union(
                    Fixtures::injectUnionCustomStoreServices()->fooInterface(),
                    Fixtures::injectUnionCustomStoreServices()->barInterface()
                ),
                'fooOrBar',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectUnionCustomStoreServices()->unionInjector()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'union from ReflectionParameter' => [
                Fixtures::injectUnionCustomStoreServices()->unionInjector(),
                'foo',
                types()->union(
                    Fixtures::injectUnionCustomStoreServices()->fooInterface(),
                    Fixtures::injectUnionCustomStoreServices()->barInterface()
                ),
                'fooOrBar',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectUnionCustomStoreServices()->unionInjector()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'union from manual setup' => [
                Fixtures::injectUnionCustomStoreServices()->unionInjector(),
                'foo',
                types()->union(
                    Fixtures::injectUnionCustomStoreServices()->fooInterface(),
                    Fixtures::injectUnionCustomStoreServices()->barInterface()
                ),
                'fooOrBar',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectUnionCustomStoreServices()->unionInjector(),
                        '__construct',
                        types()->union(
                            Fixtures::injectUnionCustomStoreServices()->fooInterface(),
                            Fixtures::injectUnionCustomStoreServices()->barInterface()
                        ),
                        'fooOrBar',
                        $attribute,
                    );
                },
            ],
            'intersect from AnnotatedTarget' => [
                Fixtures::injectIntersectCustomStoreServices()->intersectInjector(),
                'fooBar',
                types()->intersect(
                    Fixtures::injectIntersectCustomStoreServices()->fooInterface(),
                    Fixtures::injectIntersectCustomStoreServices()->barInterface(),
                ),
                'fooAndBar',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectIntersectCustomStoreServices()->intersectInjector()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'intersect from ReflectionParameter' => [
                Fixtures::injectIntersectCustomStoreServices()->intersectInjector(),
                'fooBar',
                types()->intersect(
                    Fixtures::injectIntersectCustomStoreServices()->fooInterface(),
                    Fixtures::injectIntersectCustomStoreServices()->barInterface(),
                ),
                'fooAndBar',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectIntersectCustomStoreServices()->intersectInjector()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'intersect from manual setup' => [
                Fixtures::injectIntersectCustomStoreServices()->intersectInjector(),
                'fooBar',
                types()->intersect(
                    Fixtures::injectIntersectCustomStoreServices()->fooInterface(),
                    Fixtures::injectIntersectCustomStoreServices()->barInterface(),
                ),
                'fooAndBar',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectIntersectCustomStoreServices()->intersectInjector(),
                        '__construct',
                        types()->intersect(
                            Fixtures::injectIntersectCustomStoreServices()->fooInterface(),
                            Fixtures::injectIntersectCustomStoreServices()->barInterface(),
                        ),
                        'fooAndBar',
                        $attribute,
                    );
                },
            ],
            'nullable-string from AnnotatedTarget' => [
                Fixtures::injectConstructorServices()->injectNullableStringService(),
                'stabbing science into your brain',
                types()->nullable(types()->string()),
                'maybe',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);

                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectConstructorServices()->injectNullableStringService()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'nullable-string from ReflectionParameter' => [
                Fixtures::injectConstructorServices()->injectNullableStringService(),
                'stabbing science into your brain',
                types()->nullable(types()->string()),
                'maybe',
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectConstructorServices()->injectNullableStringService()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                },
            ],
            'nullable-string from manual setup' => [
                Fixtures::injectConstructorServices()->injectNullableStringService(),
                'stabbing science into your brain',
                types()->nullable(types()->string()),
                'maybe',
                function (InjectAttribute $attribute) : InjectDefinition {
                    return $this->subject->injectDefinitionFromManualSetup(
                        Fixtures::injectConstructorServices()->injectNullableStringService(),
                        '__construct',
                        types()->nullable(types()->string()),
                        'maybe',
                        $attribute,
                    );
                },
            ]
        ];
    }

    /**
     * @param Closure(InjectAttribute):InjectDefinition $definitionCreator
     */
    #[DataProvider('injectTypeProvider')]
    public function testInjectDefinitionFromAnnotatedTargetWithTypeHasCorrectInformation(
        Type $service,
        mixed $value,
        Type|TypeUnion|TypeIntersect $type,
        string $parameterName,
        Closure $definitionCreator
    ) : void {
        $attribute = $this->createMock(InjectAttribute::class);
        $attribute->expects($this->once())->method('value')->willReturn($value);
        $attribute->expects($this->once())->method('from')->willReturn(null);
        $attribute->expects($this->once())->method('profiles')->willReturn([]);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame($service, $definition->class());
        self::assertSame('__construct', $definition->methodName());
        self::assertSame($parameterName, $definition->parameterName());
        if ($type instanceof Type) {
            self::assertSame($type, $definition->type());
        } else {
            self::assertSame($type->types(), $definition->type()->types());
        }

        self::assertSame($value, $definition->value());
        self::assertSame(['default'], $definition->profiles());
        self::assertNull($definition->storeName());
        self::assertSame($attribute, $definition->attribute());
    }

    public static function injectWithCustomAttributeDefinitionCreatorProvider() : array {
        return [
            'annotatedTarget' => [
                function (InjectAttribute $attribute) : InjectDefinition {
                    $target = $this->createMock(AnnotatedTarget::class);
                    $target->expects($this->once())->method('attributeInstance')->willReturn($attribute);
                    $target->expects($this->once())->method('targetReflection')->willReturn(
                        (new \ReflectionClass(Fixtures::injectConstructorServices()->injectArrayService()->name()))->getConstructor()?->getParameters()[0]
                    );

                    return $this->subject->injectDefinitionFromAnnotatedTarget($target);
                },
            ],
            'reflectionParameter' => [
                function (InjectAttribute $attribute) : InjectDefinition {
                    $reflection = (new \ReflectionClass(Fixtures::injectConstructorServices()->injectArrayService()->name()))->getConstructor()?->getParameters()[0];

                    return $this->subject->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
                }
            ]
        ];
    }

    #[DataProvider('injectWithCustomAttributeDefinitionCreatorProvider')]
    /**
     * @param Closure(InjectAttribute):InjectDefinition $definitionCreator
     */
    public function testInjectDefinitionFromAnnotatedTargetWithExplicitProfiles(Closure $definitionCreator) : void {
        $attribute = $this->createMock(InjectAttribute::class);

        $service = Fixtures::injectConstructorServices()->injectArrayService();

        $attribute->expects($this->once())->method('value')->willReturn(['foo', 'bar']);
        $attribute->expects($this->once())->method('from')->willReturn(null);
        $attribute->expects($this->once())->method('profiles')->willReturn(['test']);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame($service, $definition->class());
        self::assertSame('__construct', $definition->methodName());
        self::assertSame('values', $definition->parameterName());
        self::assertSame(types()->array(), $definition->type());
        self::assertSame(['foo', 'bar'], $definition->value());
        self::assertSame(['test'], $definition->profiles());
        self::assertNull($definition->storeName());
        self::assertSame($attribute, $definition->attribute());
    }

    #[DataProvider('injectWithCustomAttributeDefinitionCreatorProvider')]
    /**
     * @param Closure(InjectAttribute):InjectDefinition $definitionCreator
     */
    public function testInjectDefinitionWithExplicitWithDefinedStoreName(Closure $definitionCreator) : void {
        $attribute = $this->createMock(InjectAttribute::class);

        $service = Fixtures::injectConstructorServices()->injectArrayService();

        $attribute->expects($this->once())->method('value')->willReturn(['foo', 'bar']);
        $attribute->expects($this->once())->method('from')->willReturn('some store name');
        $attribute->expects($this->once())->method('profiles')->willReturn([]);

        $definition = ($definitionCreator->bindTo($this, $this))($attribute);

        self::assertSame($service, $definition->class());
        self::assertSame('__construct', $definition->methodName());
        self::assertSame('values', $definition->parameterName());
        self::assertSame(types()->array(), $definition->type());
        self::assertSame(['foo', 'bar'], $definition->value());
        self::assertSame(['default'], $definition->profiles());
        self::assertSame('some store name', $definition->storeName());
        self::assertSame($attribute, $definition->attribute());
    }

    public function testAliasDefinition() : void {
        $definition = $this->subject->aliasDefinition(
            Fixtures::ambiguousAliasedServices()->fooInterface(),
            Fixtures::ambiguousAliasedServices()->barImplementation()
        );

        self::assertSame(
            Fixtures::ambiguousAliasedServices()->fooInterface(),
            $definition->abstractService()
        );
        self::assertSame(
            Fixtures::ambiguousAliasedServices()->barImplementation(),
            $definition->concreteService()
        );
    }
}
