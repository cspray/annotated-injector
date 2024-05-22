<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\InvalidScanDirectories;
use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegate;
use Cspray\AnnotatedContainer\Exception\InvalidServicePrepare;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysis\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\AnotherDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\TestLogger;
use Cspray\AnnotatedContainerFixture\ConfigurationWithArrayEnum\FooEnum;
use Cspray\AnnotatedContainerFixture\ConfigurationWithEnum\MyEnum;
use Cspray\AnnotatedContainerFixture\CustomServiceAttribute\Repository;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class AnnotatedTargetContainerDefinitionAnalyzerTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private AnnotatedTargetContainerDefinitionAnalyzer $subject;
    private TestLogger $logger;

    public function setUp() : void {
        $this->logger = new TestLogger();
        $this->subject = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter($this->logger)
        );
    }

    private function runAnalysisDirectory(
        array|string $dir,
        DefinitionProvider $consumer = null
    ) : ContainerDefinition {
        if (is_string($dir)) {
            $dir = [$dir];
        }
        $options = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(...$dir)
            ->withLogger($this->logger);

        if ($consumer !== null) {
            $options = $options->withDefinitionProvider($consumer);
        }

        return $this->subject->analyze($options->build());
    }

    public function testEmptyScanDirectoriesThrowsException() : void {
        $this->expectException(InvalidScanDirectories::class);
        $this->expectExceptionMessage('ContainerDefinitionAnalysisOptions must include at least 1 directory to scan, but none were provided.');
        $this->runAnalysisDirectory([]);
    }

    public function testLogEmptyScanDirectories() : void {
        try {
            $this->runAnalysisDirectory([]);
        } catch (InvalidScanDirectories $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'ContainerDefinitionAnalysisOptions must include at least 1 directory to scan, but none were provided.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testServicePrepareNotOnServiceThrowsException() {
        $this->expectException(InvalidServicePrepare::class);
        $this->expectExceptionMessage(sprintf(
            'Service preparation defined on %s::postConstruct, but that class is not a service.',
            LogicalErrorApps\ServicePrepareNotService\FooImplementation::class
        ));
        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
    }

    public function testLogServicePrepareNotOnService() : void {
        try {
            $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ServicePrepareNotService');
        }  catch (InvalidServicePrepare $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => sprintf(
                    'Service preparation defined on %s::postConstruct, but that class is not a service.',
                    LogicalErrorApps\ServicePrepareNotService\FooImplementation::class
                ),
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testDuplicateScanDirectoriesThrowsException() {
        $this->expectException(InvalidScanDirectories::class);
        $this->expectExceptionMessage('ContainerDefinitionAnalysisOptions includes duplicate scan directories. Please pass a distinct set of directories to scan.');
        $this->runAnalysisDirectory([
            Fixtures::singleConcreteService()->getPath(),
            Fixtures::ambiguousAliasedServices()->getPath(),
            Fixtures::singleConcreteService()->getPath()
        ]);
    }

    public function testLogScanDuplicateDirectories() : void {
        try {
            $this->runAnalysisDirectory([
                Fixtures::singleConcreteService()->getPath(),
                Fixtures::singleConcreteService()->getPath(),
                Fixtures::configurationServices()->getPath()
            ]);
        } catch (InvalidScanDirectories $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'ContainerDefinitionAnalysisOptions includes duplicate scan directories. Please pass a distinct set of directories to scan.',
                'context' => [
                    'sourcePaths' => [
                        Fixtures::singleConcreteService()->getPath(),
                        Fixtures::singleConcreteService()->getPath(),
                        Fixtures::configurationServices()->getPath()
                    ]
                ]
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testImplicitServiceDelegateHasNoReturnType() {
        $this->expectException(InvalidServiceDelegate::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateNoType\FooFactory::class . '::create does not declare a service in the Attribute or as a return type of the method.'
        );

        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateNoType');
    }

    public function testLogImplicitServiceDelegateHasNoReturnType() : void {
        try {
            $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateNoType');
        }  catch (InvalidServiceDelegate $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateNoType\FooFactory::class . '::create does not declare a service in the Attribute or as a return type of the method.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testImplicitServiceDelegateHasScalarReturnType() {
        $this->expectException(InvalidServiceDelegate::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateScalarType\FooFactory::class . '::create declares a scalar value as a service type.'
        );

        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateScalarType');
    }

    public function testLogImplicitServiceDelegateHasScalarReturnType() : void {
        try {
            $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateScalarType');
        } catch (InvalidServiceDelegate $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateScalarType\FooFactory::class . '::create declares a scalar value as a service type.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testImplicitServiceDelegateHasIntersectionReturnType() {
        $this->expectException(InvalidServiceDelegate::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateIntersectionType\FooFactory::class . '::create declares an unsupported intersection as a service type.'
        );

        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateIntersectionType');
    }

    public function testLogImplicitServiceDelegateHasIntersectionReturnType() : void {
        try {
            $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateIntersectionType');
        } catch (InvalidServiceDelegate $exception) {
            // noop we expect this
        } finally {
            $expected = [
                'message' => 'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateIntersectionType\FooFactory::class . '::create declares an unsupported intersection as a service type.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testImplicitServiceDelegateHasUnionReturnType() {
        $this->expectException(InvalidServiceDelegate::class);
        $this->expectExceptionMessage(
            'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateUnionType\FooFactory::class . '::create declares an unsupported union as a service type.'
        );

        $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateUnionType');
    }

    public function testLogImplicitServiceDelegateHasUnionReturnType() {
        try {
            $this->runAnalysisDirectory(__DIR__ . '/LogicalErrorApps/ImplicitServiceDelegateUnionType');
        } catch (InvalidServiceDelegate $exception) {
            // noop, we expect this
        } finally {
            $expected = [
                'message' => 'The #[ServiceDelegate] Attribute on ' . LogicalErrorApps\ImplicitServiceDelegateUnionType\FooFactory::class . '::create declares an unsupported union as a service type.',
                'context' => []
            ];
            self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::ERROR));
        }
    }

    public function testLoggingAnalysisLifecycleStarted() : void {
        $this->runAnalysisDirectory([
            $path1 = Fixtures::singleConcreteService()->getPath(),
            $path2 = Fixtures::ambiguousAliasedServices()->getPath()
        ]);

        $expected1 = [
            'message' => sprintf('Annotated Container compiling started.'),
            'context' => []
        ];
        $expected2 = [
            'message' => sprintf('Scanning directories for Attributes: %s %s.', $path1, $path2),
            'context' => [
                'sourcePaths' => [$path1, $path2]
            ]
        ];

        $logs = $this->logger->getLogsForLevel(LogLevel::INFO);
        self::assertSame($expected1, $logs[0]);
        self::assertSame($expected2, $logs[1]);
    }

    public function testLoggingServiceDefinition() : void {
        $this->runAnalysisDirectory(Fixtures::singleConcreteService()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed ServiceDefinition from #[%s] Attribute on %s.',
                Service::class,
                Fixtures::singleConcreteService()->fooImplementation()->getName()
            ),
            'context' => [
                'attribute' => Service::class,
                'target' => [
                    'class' => Fixtures::singleConcreteService()->fooImplementation()->getName(),
                ],
                'definition' => [
                    'type' => ServiceDefinition::class,
                    'serviceType' => Fixtures::singleConcreteService()->fooImplementation()->getName(),
                    'name' => null,
                    'profiles' => ['default'],
                    'isPrimary' => false,
                    'isConcrete' => true,
                    'isAbstract' => false
                ]
            ]
        ];
        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingServiceDelegateTarget() : void {
        $this->runAnalysisDirectory(Fixtures::delegatedService()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed ServiceDelegateDefinition from #[%s] Attribute on %s::%s.',
                ServiceDelegate::class,
                Fixtures::delegatedService()->serviceFactory()->getName(),
                'createService'
            ),
            'context' => [
                'attribute' => ServiceDelegate::class,
                'target' => [
                    'class' => Fixtures::delegatedService()->serviceFactory()->getName(),
                    'method' => 'createService',
                ],
                'definition' => [
                    'type' => ServiceDelegateDefinition::class,
                    'serviceType' => Fixtures::delegatedService()->serviceInterface()->getName(),
                    'delegateType' => Fixtures::delegatedService()->serviceFactory()->getName(),
                    'delegateMethod' => 'createService'
                ]
            ]
        ];
        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingServicePrepareTarget() : void {
        $this->runAnalysisDirectory(Fixtures::classOnlyPrepareServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed ServicePrepareDefinition from #[%s] Attribute on %s::setBar.',
                ServicePrepare::class,
                Fixtures::classOnlyPrepareServices()->fooImplementation()->getName(),
            ),
            'context' => [
                'attribute' => ServicePrepare::class,
                'target' => [
                    'class' => Fixtures::classOnlyPrepareServices()->fooImplementation()->getName(),
                    'method' => 'setBar'
                ],
                'definition' => [
                    'type' => ServicePrepareDefinition::class,
                    'serviceType' => Fixtures::classOnlyPrepareServices()->fooImplementation()->getName(),
                    'prepareMethod' => 'setBar'
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingConfigurationTarget() : void {
        $this->runAnalysisDirectory(Fixtures::configurationServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed ConfigurationDefinition from #[%s] Attribute on %s.',
                Configuration::class,
                Fixtures::configurationServices()->myConfig()->getName()
            ),
            'context' => [
                'attribute' => Configuration::class,
                'target' => [
                    'class' => Fixtures::configurationServices()->myConfig()->getName(),
                ],
                'definition' => [
                    'type' => ConfigurationDefinition::class,
                    'configurationType' => Fixtures::configurationServices()->myConfig()->getName(),
                    'name' => null
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectMethodParameter() : void {
        $this->runAnalysisDirectory(Fixtures::injectConstructorServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed InjectDefinition from #[%s] Attribute on %s::%s(%s).',
                Inject::class,
                Fixtures::injectConstructorServices()->injectStringService()->getName(),
                '__construct',
                'val'
            ),
            'context' => [
                'attribute' => Inject::class,
                'target' => [
                    'class' => Fixtures::injectConstructorServices()->injectStringService()->getName(),
                    'method' => '__construct',
                    'parameter' => 'val'
                ],
                'definition' => [
                    'type' => InjectDefinition::class,
                    'serviceType' => Fixtures::injectConstructorServices()->injectStringService()->getName(),
                    'method' => '__construct',
                    'parameterType' => 'string',
                    'parameter' => 'val',
                    'value' => 'foobar',
                    'store' => null,
                    'profiles' => ['default']
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectConfigurationProperty() : void {
        $this->runAnalysisDirectory(Fixtures::configurationServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed InjectDefinition from #[%s] Attribute on %s::%s.',
                Inject::class,
                Fixtures::configurationServices()->myConfig()->getName(),
                'user'
            ),
            'context' => [
                'attribute' => Inject::class,
                'target' => [
                    'class' => Fixtures::configurationServices()->myConfig()->getName(),
                    'property' => 'user'
                ],
                'definition' => [
                    'type' => InjectDefinition::class,
                    'serviceType' => Fixtures::configurationServices()->myConfig()->getName(),
                    'property' => 'user',
                    'propertyType' => 'string',
                    'value' => 'USER',
                    'store' => 'env',
                    'profiles' => ['default']
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingAddAliasDefinitions() : void {
        $this->runAnalysisDirectory(Fixtures::implicitAliasedServices()->getPath());

        $expected = [
            'message' => sprintf(
                'Added alias for abstract service %s to concrete service %s.',
                Fixtures::implicitAliasedServices()->fooInterface()->getName(),
                Fixtures::implicitAliasedServices()->fooImplementation()->getName()
            ),
            'context' => [
                'abstractService' => Fixtures::implicitAliasedServices()->fooInterface()->getName(),
                'concreteService' => Fixtures::implicitAliasedServices()->fooImplementation()->getName()
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingNoThirdPartyServices() : void {
        $this->runAnalysisDirectory(Fixtures::singleConcreteService()->getPath());

        $expected = [
            'message' => sprintf(
                'No %s was provided.',
                DefinitionProvider::class
            ),
            'context' => []
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingThirdPartyServices() : void {
        $this->runAnalysisDirectory(
            Fixtures::singleConcreteService()->getPath(),
            new StubDefinitionProvider()
        );

        $expected = [
            'message' => sprintf(
                'Added services from %s to ContainerDefinition.',
                StubDefinitionProvider::class
            ),
            'context' => [
                'definitionProvider' => StubDefinitionProvider::class
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingFromCompositeDefinitionProvider() : void {
        $this->runAnalysisDirectory(
            Fixtures::singleConcreteService()->getPath(),
            new CompositeDefinitionProvider(
                new StubDefinitionProvider(),
                new AnotherDefinitionProvider()
            )
        );

        $expectedProvider = sprintf(
            'Composite<%s, %s>',
            StubDefinitionProvider::class,
            AnotherDefinitionProvider::class
        );
        $expected = [
            'message' => sprintf(
                'Added services from %s to ContainerDefinition.',
                $expectedProvider
            ),
            'context' => [
                'definitionProvider' => $expectedProvider
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLastLoggingMessageIsCompilingFinished() : void {
        $this->runAnalysisDirectory(
            Fixtures::singleConcreteService()->getPath(),
        );

        $expected = [
            'message' => 'Annotated Container compiling finished.',
            'context' => []
        ];

        $logs = $this->logger->getLogsForLevel(LogLevel::INFO);

        self::assertEquals(
            $expected,
            $logs[count($logs) - 1]
        );
    }

    public function testServiceDelegateNotServiceAddsImplicitConcreteService() : void {
        $containerDef = $this->runAnalysisDirectory(Fixtures::beanLikeConfigConcrete()->getPath());

        $serviceDef = $this->getServiceDefinition(
            $containerDef->getServiceDefinitions(),
            Fixtures::beanLikeConfigConcrete()->fooService()->getName()
        );

        self::assertNotNull($serviceDef);
        self::assertSame($serviceDef->getType(), Fixtures::beanLikeConfigConcrete()->fooService());
        self::assertSame(['default'], $serviceDef->getProfiles());
        self::assertNull($serviceDef->getName());
        self::assertFalse($serviceDef->isPrimary());
        self::assertTrue($serviceDef->isConcrete());
        self::assertFalse($serviceDef->isAbstract());
    }

    public function testServiceDelegateNotServiceAddsImplicitAbstractInterfaceService() : void {
        $containerDef = $this->runAnalysisDirectory(Fixtures::beanLikeConfigInterface()->getPath());

        $serviceDef = $this->getServiceDefinition(
            $containerDef->getServiceDefinitions(),
            Fixtures::beanLikeConfigInterface()->fooInterface()->getName()
        );

        self::assertNotNull($serviceDef);
        self::assertSame($serviceDef->getType(), Fixtures::beanLikeConfigInterface()->fooInterface());
        self::assertSame(['default'], $serviceDef->getProfiles());
        self::assertNull($serviceDef->getName());
        self::assertFalse($serviceDef->isPrimary());
        self::assertFalse($serviceDef->isConcrete());
        self::assertTrue($serviceDef->isAbstract());
    }

    public function testServiceDelegateNotServiceAddsImplicitAbstractClassService() : void {
        $containerDef = $this->runAnalysisDirectory(Fixtures::beanLikeConfigAbstract()->getPath());

        $serviceDef = $this->getServiceDefinition(
            $containerDef->getServiceDefinitions(),
            Fixtures::beanLikeConfigAbstract()->abstractFooService()->getName()
        );

        self::assertNotNull($serviceDef);
        self::assertSame($serviceDef->getType(), Fixtures::beanLikeConfigAbstract()->abstractFooService());
        self::assertSame(['default'], $serviceDef->getProfiles());
        self::assertNull($serviceDef->getName());
        self::assertFalse($serviceDef->isPrimary());
        self::assertFalse($serviceDef->isConcrete());
        self::assertTrue($serviceDef->isAbstract());
    }

    public function testLogCustomAttribute() : void {

        $this->runAnalysisDirectory(Fixtures::customServiceAttribute()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed ServiceDefinition from #[%s] Attribute on %s.',
                Repository::class,
                Fixtures::customServiceAttribute()->myRepo()->getName()
            ),
            'context' => [
                'attribute' => Repository::class,
                'target' => [
                    'class' => Fixtures::customServiceAttribute()->myRepo()->getName(),
                ],
                'definition' => [
                    'type' => ServiceDefinition::class,
                    'serviceType' => Fixtures::customServiceAttribute()->myRepo()->getName(),
                    'name' => null,
                    'profiles' => ['test'],
                    'isPrimary' => false,
                    'isConcrete' => true,
                    'isAbstract' => false
                ]
            ]
        ];
        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectConfigurationPropertyEnum() : void {
        $this->runAnalysisDirectory(Fixtures::configurationWithEnum()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed InjectDefinition from #[%s] Attribute on %s::enum.',
                Inject::class,
                Fixtures::configurationWithEnum()->configuration()->getName(),
            ),
            'context' => [
                'attribute' => Inject::class,
                'target' => [
                    'class' => Fixtures::configurationWithEnum()->configuration()->getName(),
                    'property' => 'enum'
                ],
                'definition' => [
                    'type' => InjectDefinition::class,
                    'serviceType' => Fixtures::configurationWithEnum()->configuration()->getName(),
                    'property' => 'enum',
                    'propertyType' => MyEnum::class,
                    'value' => MyEnum::class . '::Foo',
                    'store' => null,
                    'profiles' => ['default']
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectArrayConfigurationPropertyEnum() : void {
        $this->runAnalysisDirectory(Fixtures::configurationWithArrayEnum()->getPath());

        $expected = [
            'message' => sprintf(
                'Parsed InjectDefinition from #[%s] Attribute on %s::cases.',
                Inject::class,
                Fixtures::configurationWithArrayEnum()->myConfiguration()->getName(),
            ),
            'context' => [
                'attribute' => Inject::class,
                'target' => [
                    'class' => Fixtures::configurationWithArrayEnum()->myConfiguration()->getName(),
                    'property' => 'cases'
                ],
                'definition' => [
                    'type' => InjectDefinition::class,
                    'serviceType' => Fixtures::configurationWithArrayEnum()->myConfiguration()->getName(),
                    'property' => 'cases',
                    'propertyType' => 'array',
                    'value' => [FooEnum::class . '::Bar', FooEnum::class . '::Qux'],
                    'store' => null,
                    'profiles' => ['default']
                ]
            ]
        ];

        self::assertContains($expected, $this->logger->getLogsForLevel(LogLevel::INFO));
    }
}