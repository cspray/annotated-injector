<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Definition\Cache;

use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\StaticAnalysis\CompositeDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CacheKeyTest extends TestCase {

    public static function optionsCacheKeyProvider() : array {
        return [
            [ContainerDefinitionAnalysisOptionsBuilder::scanDirectories('dir')->build(), md5('dir')],
            [ContainerDefinitionAnalysisOptionsBuilder::scanDirectories('foo', 'bar', 'baz')->build(), md5('barbazfoo')],
            [ContainerDefinitionAnalysisOptionsBuilder::scanDirectories('foo')
                ->withDefinitionProvider(new StubDefinitionProvider())->build(), md5('foo/' . StubDefinitionProvider::class)],
            [ContainerDefinitionAnalysisOptionsBuilder::scanDirectories('qux', 'quz', 'quy')
                ->withDefinitionProvider(new StubDefinitionProvider())->build(), md5('quxquyquz/' . StubDefinitionProvider::class)],
            [ContainerDefinitionAnalysisOptionsBuilder::scanDirectories('zux', 'zuw', 'zuv')
                ->withDefinitionProvider($composite = new CompositeDefinitionProvider(new StubDefinitionProvider(), new StubDefinitionProvider()))->build(), md5('zuvzuwzux/' . $composite)]
        ];
    }

    #[DataProvider('optionsCacheKeyProvider')]
    public function testCreatedKeyIsExpectedFromAnalysisOptionsProvided(
        ContainerDefinitionAnalysisOptions $options,
        string $expectedKey
    ) : void {
        $actual = CacheKey::fromContainerDefinitionAnalysisOptions($options);

        self::assertSame($expectedKey, $actual->asString());
    }
}
