<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysis\CacheAwareContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheAwareContainerDefinitionAnalyzerTest extends TestCase {

    private AnnotatedTargetContainerDefinitionAnalyzer $annotatedTargetContainerDefinitionAnalyzer;
    private MockObject&ContainerDefinitionCache $cache;

    protected function setUp(): void {
        $this->cache = $this->getMockBuilder(ContainerDefinitionCache::class)->getMock();
        $this->annotatedTargetContainerDefinitionAnalyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter(),
            new Emitter()
        );
    }

    public function testContainerDefinitionCacheHitDoesNotCallUnderlyingAnalyzer() {
        $dir = Fixtures::implicitAliasedServices()->getPath();
        $analysisOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir)->build();
        $containerDefinition = $this->annotatedTargetContainerDefinitionAnalyzer->analyze($analysisOptions);
        $this->cache->expects($this->once())
            ->method('get')
            ->with($this->callback(
                fn(CacheKey $cacheKey) =>
                    $cacheKey->asString() === CacheKey::fromContainerDefinitionAnalysisOptions($analysisOptions)->asString()
            ))
            ->willReturn($containerDefinition);

        $mock = $this->getMockBuilder(ContainerDefinitionAnalyzer::class)->getMock();
        $mock->expects($this->never())->method('analyze');
        $subject = new CacheAwareContainerDefinitionAnalyzer(
            $mock,
            $this->cache
        );

        $actual = $subject->analyze($analysisOptions);

        self::assertSame($containerDefinition, $actual);
    }

    public function testContainerDefinitionCacheMissesDoesCallUnderlyingAnalyzer() : void {
        $dir = Fixtures::implicitAliasedServices()->getPath();
        $analysisOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir)->build();
        $containerDefinition = $this->annotatedTargetContainerDefinitionAnalyzer->analyze($analysisOptions);

        $expectedCacheKey = CacheKey::fromContainerDefinitionAnalysisOptions($analysisOptions);
        $this->cache->expects($this->once())
            ->method('get')
            ->with($this->callback(
                fn(CacheKey $cacheKey) =>
                    $cacheKey->asString() === $expectedCacheKey->asString()
            ))
            ->willReturn(null);
        $this->cache->expects($this->once())
            ->method('set')
            ->with(
                $this->callback(
                    fn(CacheKey $cacheKey) =>
                        $cacheKey->asString() === $expectedCacheKey->asString()
                ),
                $containerDefinition
            );

        $mock = $this->getMockBuilder(ContainerDefinitionAnalyzer::class)->getMock();
        $mock->expects($this->once())->method('analyze')->with($analysisOptions)->willReturn($containerDefinition);
        $subject = new CacheAwareContainerDefinitionAnalyzer(
            $mock,
            $this->cache
        );

        $actual = $subject->analyze($analysisOptions);

        self::assertSame($containerDefinition, $actual);
    }
}
