<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\CacheAwareBootstrappingConfiguration;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CacheAwareBootstrappingConfigurationTest extends TestCase {

    public static function nonCacheMethodsProvider() : array {
        return [
            'scanDirectories' => ['scanDirectories', ['dir']],
            'containerDefinitionProvider' => ['containerDefinitionProvider', new StubDefinitionProvider()],
            'parameterStores' => ['parameterStores', [new StubParameterStore()]]
        ];
    }

    #[DataProvider('nonCacheMethodsProvider')]
    public function testNonCacheMethodsDelegatedToInjectedConfiguration(string $method, mixed $value) : void {
        $configuration = $this->getMockBuilder(BootstrappingConfiguration::class)->getMock();
        $configuration->expects($this->once())->method($method)->willReturn($value);

        $cache = $this->getMockBuilder(ContainerDefinitionCache::class)->getMock();

        $subject = new CacheAwareBootstrappingConfiguration($configuration, $cache);

        self::assertSame($value, $subject->$method());
    }

    public function testInjectedCacheReturned() : void {
        $configuration = $this->getMockBuilder(BootstrappingConfiguration::class)->getMock();
        $cache = $this->getMockBuilder(ContainerDefinitionCache::class)->getMock();
        $subject = new CacheAwareBootstrappingConfiguration($configuration, $cache);

        self::assertSame($cache, $subject->cache());
    }
}
