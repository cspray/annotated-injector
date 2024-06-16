<?php

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\DefaultDefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefaultParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\XmlBootstrappingConfiguration;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainer\StaticAnalysis\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\stringType;

class XmlBootstrappingConfigurationTest extends TestCase {

    private MockObject&Filesystem $filesystem;

    protected function setUp() : void {
        $this->filesystem = $this->createMock(Filesystem::class);
    }

    private function mockFilePresentFilesystemInteractions(
        string $path,
        string $contents
    ) : void {
        $this->filesystem->expects($this->once())
            ->method('isFile')
            ->with($path)
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('read')
            ->with($path)
            ->willReturn($contents);
    }

    public function testXmlDoesNotValidateSchemaThrowsError() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer />
XML;

        $this->mockFilePresentFilesystemInteractions('/my/path/to/annotated-container.xml', $badXml);

        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage(
            'Configuration file /my/path/to/annotated-container.xml does not validate against the appropriate schema.'
        );

        new XmlBootstrappingConfiguration(
            $this->filesystem,
            '/my/path/to/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );
    }

    public function testValidXmlReturnsScanDirectories() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
    <scanDirectories>
        <source>
            <dir>src</dir>
            <dir>test/helper</dir>
            <dir>lib</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        $this->mockFilePresentFilesystemInteractions(
            '/path/to/annotated-container.xml',
            $goodXml
        );

        $configuration = new XmlBootstrappingConfiguration(
            $this->filesystem,
            '/path/to/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );

        self::assertSame(
            ['src', 'test/helper', 'lib'],
            $configuration->scanDirectories()
        );
    }

    public function testValidXmlReturnsDefinitionProvider() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
    <definitionProviders>
        <definitionProvider>Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider</definitionProvider>
    </definitionProviders>
</annotatedContainer>
XML;

        $this->mockFilePresentFilesystemInteractions(
            '/path/annotated-container.xml',
            $goodXml
        );

        $configuration = new XmlBootstrappingConfiguration(
            $this->filesystem,
            '/path/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );
        $provider = $configuration->containerDefinitionProvider();
        self::assertInstanceOf(
            CompositeDefinitionProvider::class,
            $provider
        );
        self::assertContainsOnlyInstancesOf(
            StubDefinitionProvider::class,
            $provider->getDefinitionProviders()
        );
    }

    public function testDefinitionProviderEmptyIfNoneDefined() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        $this->mockFilePresentFilesystemInteractions(
            'annotated-container.xml',
            $goodXml
        );

        $config = new XmlBootstrappingConfiguration(
            $this->filesystem,
            'annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );

        self::assertNull($config->containerDefinitionProvider());
    }

    public function testCacheDirNotSpecifiedReturnsNull() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        $this->mockFilePresentFilesystemInteractions('annotated-container.xml', $goodXml);

        $config = new XmlBootstrappingConfiguration(
            $this->filesystem,
            'annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );
        self::assertNull($config->cache());
    }

    public function testCacheIsAlwaysNull() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        $this->mockFilePresentFilesystemInteractions('annotated-container.xml', $goodXml);

        $config = new XmlBootstrappingConfiguration(
            $this->filesystem,
            'annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );

        self::assertNull($config->cache());
    }

    public function testParameterStoresReturned() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <parameterStores>
    <parameterStore>Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore</parameterStore>
  </parameterStores>
</annotatedContainer>
XML;

        $this->mockFilePresentFilesystemInteractions('annotated-container.xml', $goodXml);

        $config = new XmlBootstrappingConfiguration(
            $this->filesystem,
            'annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );

        self::assertCount(1, $config->parameterStores());
        self::assertContainsOnlyInstancesOf(StubParameterStore::class, $config->parameterStores());
    }

    public function testParameterStoreFactoryPresentRespected() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <parameterStores>
    <parameterStore>Cspray\AnnotatedContainer\Unit\Helper\StubParameterStoreWithDependencies</parameterStore>
  </parameterStores>
</annotatedContainer>
XML;

        $parameterStoreFactory = new class implements ParameterStoreFactory {

            /**
             * @param class-string<ParameterStore> $identifier
             * @return ParameterStore
             */
            public function createParameterStore(string $identifier) : ParameterStore {
                return new $identifier('passed to constructor ');
            }
        };

        $this->mockFilePresentFilesystemInteractions('annotated-container.xml', $goodXml);

        $config = new XmlBootstrappingConfiguration(
            $this->filesystem,
            'annotated-container.xml',
            $parameterStoreFactory,
            new DefaultDefinitionProviderFactory(),
        );

        self::assertCount(1, $config->parameterStores());
        self::assertSame('passed to constructor my-key', $config->parameterStores()[0]->fetch(stringType(), 'my-key'));
    }

    public function testDefinitionProviderFactoryPresentRespected() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
  <scanDirectories>
    <source>
      <dir>src</dir>
    </source>
  </scanDirectories>
  <definitionProviders>
    <definitionProvider>Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies</definitionProvider>
  </definitionProviders>
</annotatedContainer>
XML;

        $consumerFactory = new class implements DefinitionProviderFactory {
            public function createProvider(string $identifier) : DefinitionProvider {
                return new $identifier(Fixtures::thirdPartyServices()->fooInterface());
            }
        };

        $this->mockFilePresentFilesystemInteractions('annotated-container.xml', $goodXml);

        $config = new XmlBootstrappingConfiguration(
            $this->filesystem,
            'annotated-container.xml',
            new DefaultParameterStoreFactory(),
            $consumerFactory
        );

        $provider = $config->containerDefinitionProvider();
        self::assertInstanceOf(CompositeDefinitionProvider::class, $provider);
        self::assertContainsOnlyInstancesOf(StubDefinitionProviderWithDependencies::class, $provider->getDefinitionProviders());
    }

    public function testVendorScanDirectoriesIncludedInList() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
    <scanDirectories>
        <source>
            <dir>src</dir>
            <dir>test/helper</dir>
            <dir>lib</dir>
        </source>
        <vendor>
          <package>
            <name>package/one</name>
            <source>
              <dir>src</dir>
              <dir>lib</dir>
            </source>
          </package>
          <package>
            <name>package/two</name>
            <source>
              <dir>other_src</dir>
            </source>
          </package>
        </vendor>
    </scanDirectories>
</annotatedContainer>
XML;

        $this->mockFilePresentFilesystemInteractions('annotated-container.xml', $goodXml);

        $configuration = new XmlBootstrappingConfiguration(
            $this->filesystem,
            'annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory()
        );

        self::assertSame(
            ['src', 'test/helper', 'lib', 'vendor/package/one/src', 'vendor/package/one/lib', 'vendor/package/two/other_src'],
            $configuration->scanDirectories()
        );
    }

    public function testConfigurationFileNotPresentThrowsException() : void {
        $this->filesystem->expects($this->once())
            ->method('isFile')
            ->with('annotated-container.xml')
            ->willReturn(false);

        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage('Provided configuration file annotated-container.xml does not exist.');

        new XmlBootstrappingConfiguration(
            $this->filesystem,
            'annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory()
        );
    }
}
