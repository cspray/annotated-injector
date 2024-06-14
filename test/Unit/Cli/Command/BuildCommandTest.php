<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Command\BuildCommand;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubInput;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuildCommandTest extends TestCase {

    private InMemoryOutput $stdout;
    private InMemoryOutput $stderr;
    private TerminalOutput $output;
    private FixtureBootstrappingDirectoryResolver $directoryResolver;
    private BuildCommand $subject;
    private MockObject&ContainerDefinitionAnalysisOptions $analysisOptions;
    private MockObject&ContainerDefinitionCache $cache;

    protected function setUp() : void {
        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->output = new TerminalOutput($this->stdout, $this->stderr);

        $this->cache = $this->createMock(ContainerDefinitionCache::class);
        $this->analysisOptions = $this->createMock(ContainerDefinitionAnalysisOptions::class);
        $this->directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $this->subject = new BuildCommand(
            $this->cache,
            $this->analysisOptions
        );
    }

    public function testName() : void {
        self::assertSame('build', $this->subject->name());
    }

    public function testSummary() : void {
        $expected = 'Analyze and cache a ContainerDefinition according to your annotated-container CLI script.';

        self::assertSame($expected, $this->subject->summary());
    }

    public function testGetHelp() : void {
        $expected = <<<SHELL
NAME

    build - Analyze and cache a ContainerDefinition according to your annotated-container CLI script.
    
SYNOPSIS

    <bold>build</bold>

DESCRIPTION

    <bold>build</bold> will analyze and cache a ContainerDefinition based on the 
    configuration file provided in your annotated-container CLI script. Using the 
    ContainerDefinitionCache provided in this script the following actions will  
    be performed:
    
    - A CacheKey will be generated based on the configuration's scan directories 
    and DefinitionProvider, if present.
    - Whatever entry for the generated CacheKey will be removed from the provided 
    ContainerDefinitionCache.
    - A CacheAwareContainerDefinitionAnalyzer, using an AnnotatedTargetContainerDefinitionAnalyzer 
    as its delegate, will analyze and cache your ContainerDefinition according to the 
    configuration provided.

SHELL;

        self::assertSame($expected, $this->subject->help());
    }

    public function testBuildRemovesAndSetsCorrectCacheEntryAndSendsCorrectOutput() : void {
        $this->analysisOptions->method('scanDirectories')
            ->willReturn([$this->directoryResolver->rootPath('SingleConcreteService')]);

        $this->analysisOptions->method('definitionProvider')
            ->willReturn(null);

        $cacheKeyMatchesExpected = function (CacheKey $cacheKey) {
            return md5($this->directoryResolver->rootPath('SingleConcreteService'))
                === $cacheKey->asString();
        };

        $this->cache->expects($this->once())
            ->method('remove')
            ->with($this->callback($cacheKeyMatchesExpected));

        $this->cache->expects($this->once())
            ->method('set')
            ->with(
                $this->callback($cacheKeyMatchesExpected),
                $this->isInstanceOf(ContainerDefinition::class)
            );

        $input = new StubInput([], ['build']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        $expected = <<<SHELL
\033[32mSuccessfully built and cached your Container!\033[0m

SHELL;

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }
}
