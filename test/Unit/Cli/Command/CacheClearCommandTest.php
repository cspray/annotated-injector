<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Command\CacheClearCommand;
use Cspray\AnnotatedContainer\Cli\Exception\CacheDirConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\CacheDirNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\ConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\InvalidOptionType;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubInput;
use Cspray\AnnotatedContainerFixture\Fixtures;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CacheClearCommandTest extends TestCase {

    private InMemoryOutput $stdout;
    private InMemoryOutput $stderr;
    private TerminalOutput $output;

    private MockObject&ContainerDefinitionCache $cache;
    private MockObject&ContainerDefinitionAnalysisOptions $analysisOptions;
    private FixtureBootstrappingDirectoryResolver $directoryResolver;

    private CacheClearCommand $subject;


    protected function setUp() : void {
        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->output = new TerminalOutput($this->stdout, $this->stderr);
        $this->directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $this->cache = $this->createMock(ContainerDefinitionCache::class);
        $this->analysisOptions = $this->createMock(ContainerDefinitionAnalysisOptions::class);

        $this->subject = new CacheClearCommand(
            $this->cache,
            $this->analysisOptions
        );
    }

    public function testGetName() : void {
        self::assertSame('cache-clear', $this->subject->name());
    }

    public function testGetHelp() : void {
        $expected = <<<SHELL
NAME

    cache-clear - Remove cached ContainerDefinition, forcing rebuild of your Container
    
SYNOPSIS

    <bold>cache-clear</bold> [OPTION]...

DESCRIPTION

    <bold>cache-clear</bold> ensures that a ContainerDefinition previously compiled
    with build, or by bootstrapping your app, is removed from a configured cache. 
    This ensures the next time your ContainerDefinition is compiled it runs static 
    analysis again.
    
    If you do not have a cacheDir configured this command will error.

OPTIONS

    --config-file="file-path.xml"

        Set the name of the configuration file to be used. If not provided the
        default value will be "annotated-container.xml".

SHELL;

        self::assertSame($expected, $this->subject->help());
    }

    public function testCallingCommandCallsCacheRemoveWithCorrectCacheKey() : void {
        $this->analysisOptions->method('scanDirectories')
            ->willReturn([$this->directoryResolver->rootPath('SingleConcreteService')]);

        $this->analysisOptions->method('definitionProvider')
            ->willReturn(null);

        $this->cache->expects($this->once())
            ->method('remove')
            ->with($this->callback(
                fn (CacheKey $cacheKey) =>
                    $cacheKey->asString() === md5($this->directoryResolver->rootPath('SingleConcreteService')))
            );

        $input = new StubInput([], ['cache-clear']);
        $this->subject->handle($input, $this->output);
    }

    

}
