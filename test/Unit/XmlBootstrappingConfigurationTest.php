<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Bootstrap\DefaultDefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefaultParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\XmlBootstrappingConfiguration;
use Cspray\AnnotatedContainer\StaticAnalysis\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore;
use Cspray\AnnotatedContainerFixture\Fixtures;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\stringType;

class XmlBootstrappingConfigurationTest extends TestCase {

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testXmlDoesNotValidateSchemaThrowsError() : void {
        $badXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer />
XML;
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($badXml)
            ->at($this->vfs);
        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage(
            'Configuration file vfs://root/annotated-container.xml does not validate against the appropriate schema.'
        );
        new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );
    }

    public function testValidXmlReturnsScanDirectories() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
            <dir>test/helper</dir>
            <dir>lib</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);
        $configuration = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );

        self::assertSame(
            ['src', 'test/helper', 'lib'],
            $configuration->getScanDirectories()
        );
    }

    public function testValidXmlReturnsDefinitionProvider() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
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
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $configuration = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );
        $provider = $configuration->getContainerDefinitionProvider();
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
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );

        self::assertNull($config->getContainerDefinitionProvider());
    }

    public function testCacheDirNotSpecifiedReturnsNull() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );
        self::assertNull($config->getCacheDirectory());
    }

    public function testCacheDirSpecifiedIsReturned() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>src</dir>
        </source>
    </scanDirectories>
    <cacheDir>cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );
        self::assertSame('cache', $config->getCacheDirectory());
    }

    public function testParameterStoresReturned() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
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

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
        );

        self::assertCount(1, $config->getParameterStores());
        self::assertContainsOnlyInstancesOf(StubParameterStore::class, $config->getParameterStores());
    }

    public function testParameterStoreFactoryPresentRespected() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
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

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            $parameterStoreFactory,
            new DefaultDefinitionProviderFactory(),
        );

        self::assertCount(1, $config->getParameterStores());
        self::assertSame('passed to constructor my-key', $config->getParameterStores()[0]->fetch(stringType(), 'my-key'));
    }

    public function testDefinitionProviderFactoryPresentRespected() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
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

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $config = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            $consumerFactory
        );

        $provider = $config->getContainerDefinitionProvider();
        self::assertInstanceOf(CompositeDefinitionProvider::class, $provider);
        self::assertContainsOnlyInstancesOf(StubDefinitionProviderWithDependencies::class, $provider->getDefinitionProviders());
    }

    public function testVendorScanDirectoriesIncludedInList() : void {
        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
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
        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);
        $configuration = new XmlBootstrappingConfiguration(
            'vfs://root/annotated-container.xml',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory()
        );

        self::assertSame(
            ['src', 'test/helper', 'lib', 'vendor/package/one/src', 'vendor/package/one/lib', 'vendor/package/two/other_src'],
            $configuration->getScanDirectories()
        );
    }

    public function testConfigurationFileNotPresentThrowsException() : void {
        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage('Provided configuration file vfs://root/not-found does not exist.');

        new XmlBootstrappingConfiguration(
            'vfs://root/not-found',
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory()
        );
    }
}
