<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfigurationProvider;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\CacheAwareBootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\CacheAwareBootstrappingConfigurationProvider;
use Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use PHPUnit\Framework\TestCase;

final class CacheAwareBootstrappingConfigurationProviderTest extends TestCase {

    public function testProvidedConfigurationIsCacheAwareConfiguration() : void {
        $directoryResolver = $this->getMockBuilder(BootstrappingDirectoryResolver::class)->getMock();
        $parameterStoreFactory = $this->getMockBuilder(ParameterStoreFactory::class)->getMock();
        $definitionProviderFactory = $this->getMockBuilder(DefinitionProviderFactory::class)->getMock();

        $bootstrappingConfig = $this->getMockBuilder(BootstrappingConfiguration::class)->getMock();
        $bootstrappingConfigProvider = $this->getMockBuilder(BootstrappingConfigurationProvider::class)->getMock();
        $bootstrappingConfigProvider->expects($this->once())
            ->method('bootstrappingConfiguration')
            ->with($directoryResolver, $parameterStoreFactory, $definitionProviderFactory)
            ->willReturn($bootstrappingConfig);

        $cache = $this->getMockBuilder(ContainerDefinitionCache::class)->getMock();

        $subject = (new CacheAwareBootstrappingConfigurationProvider(
            $bootstrappingConfigProvider,
            $cache
        ))->bootstrappingConfiguration($directoryResolver, $parameterStoreFactory, $definitionProviderFactory);

        self::assertInstanceOf(CacheAwareBootstrappingConfiguration::class, $subject);
        self::assertSame($cache, $subject->cache());
    }
}
