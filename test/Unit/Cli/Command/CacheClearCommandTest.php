<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Command\CacheClearCommand;
use Cspray\AnnotatedContainer\Cli\Exception\CacheDirConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\CacheDirNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\ConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\InvalidOptionType;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\InMemoryOutput;
use Cspray\AnnotatedContainer\Unit\Helper\StubInput;
use Cspray\AnnotatedContainerFixture\Fixtures;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;

final class CacheClearCommandTest extends TestCase {

    private InMemoryOutput $stdout;
    private InMemoryOutput $stderr;
    private TerminalOutput $output;

    private CacheClearCommand $subject;

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        $this->markTestIncomplete('Need to redesign how the CacheClear command gets the cache. Reliant on resolving #385.');
        $this->stdout = new InMemoryOutput();
        $this->stderr = new InMemoryOutput();
        $this->output = new TerminalOutput($this->stdout, $this->stderr);

        $this->subject = new CacheClearCommand(
            new FixtureBootstrappingDirectoryResolver()
        );

        $this->vfs = VirtualFilesystem::setup();
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

    public function testConfigurationFileNotFoundThrowsException() : void {
        self::expectException(ConfigurationNotFound::class);
        self::expectExceptionMessage('No configuration file found at "annotated-container.xml".');

        $input = new StubInput([], ['cache-clear']);
        $this->subject->handle($input, $this->output);
    }

    public function testConfigurationFileDoesNotHaveCacheDirThrowsException() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        self::expectException(CacheDirConfigurationNotFound::class);
        self::expectExceptionMessage('Clearing a cache without a cache directory configured is not supported.');

        $input = new StubInput([], ['cache-clear']);
        $this->subject->handle($input, $this->output);
    }

    public function testCacheDirConfigureNotFoundThrowsException() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        self::assertDirectoryDoesNotExist('vfs://root/.annotated-container-cache');
        self::expectException(CacheDirNotFound::class);
        self::expectExceptionMessage('The cache directory configured ".annotated-container-cache" could not be found.');

        $input = new StubInput([], ['cache-clear']);
        $this->subject->handle($input, $this->output);
    }

    public function testCacheClearDirectoryExistsNoFile() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        VirtualFilesystem::newDirectory('.annotated-container-cache')
            ->at($this->vfs);

        $input = new StubInput([], ['cache-clear']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);

        $expected = <<<SHELL
\033[32mAnnotated Container cache has been cleared.\033[0m

SHELL;

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testCachedContainerPresentIsRemoved() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($config)
            ->at($this->vfs);

        $cacheDir = VirtualFilesystem::newDirectory('.annotated-container-cache')
            ->at($this->vfs);

        $expectedKey = md5(Fixtures::singleConcreteService()->getPath());

        VirtualFilesystem::newFile($expectedKey)
            ->withContent('does not matter here')
            ->at($cacheDir);

        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expectedKey);

        $input = new StubInput([], ['cache-clear']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        self::assertFileDoesNotExist('vfs://root/.annotated-container-cache/' . $expectedKey);

        $expected = <<<SHELL
\033[32mAnnotated Container cache has been cleared.\033[0m

SHELL;

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testConfigFileOptionRespected() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>SingleConcreteService</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('my-config.xml')
            ->withContent($config)
            ->at($this->vfs);

        $cacheDir = VirtualFilesystem::newDirectory('.annotated-container-cache')
            ->at($this->vfs);

        $expectedKey = md5(Fixtures::singleConcreteService()->getPath());

        VirtualFilesystem::newFile($expectedKey)
            ->withContent('does not matter here')
            ->at($cacheDir);

        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expectedKey);

        $input = new StubInput(['config-file' => 'my-config.xml'], ['cache-clear']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        self::assertFileDoesNotExist('vfs://root/.annotated-container-cache/' . $expectedKey);

        $expected = <<<SHELL
\033[32mAnnotated Container cache has been cleared.\033[0m

SHELL;

        self::assertSame($expected, $this->stdout->getContentsAsString());
    }

    public function testCacheClearRespectsConfigFileFromComposerJson() : void {
        $config = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>AmbiguousAliasedServices</dir>
    </source>
  </scanDirectories>
  <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('composer.json')
            ->withContent(json_encode([
                'extra' => [
                    'annotatedContainer' => [
                        'configFile' => 'composer-config.xml'
                    ]
                ]
            ]))->at($this->vfs);

        VirtualFilesystem::newFile('composer-config.xml')
            ->withContent($config)
            ->at($this->vfs);

        $cacheDir = VirtualFilesystem::newDirectory('.annotated-container-cache')->at($this->vfs);

        $expectedKey = md5(Fixtures::singleConcreteService()->getPath());

        VirtualFilesystem::newFile($expectedKey)
            ->withContent('does not matter here')
            ->at($cacheDir);

        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expectedKey);

        $input = new StubInput([], ['cache-clear']);
        $exitCode = $this->subject->handle($input, $this->output);

        self::assertSame(0, $exitCode);
        $expectedKey = md5(Fixtures::ambiguousAliasedServices()->getPath());
        self::assertFileDoesNotExist('vfs://root/.annotated-container-cache/' . $expectedKey);
    }

    public function testCommandConfigFileBooleansThrowsException() : void {
        self::expectException(InvalidOptionType::class);
        self::expectExceptionMessage('The option "config-file" MUST NOT be a flag-only option.');

        $input = new StubInput(['config-file' => true], ['cache-clear']);
        $this->subject->handle($input, $this->output);
    }

    public function testCommandConfigFileArrayThrowsException() : void {
        self::expectException(InvalidOptionType::class);
        self::expectExceptionMessage('The option "config-file" MUST NOT be provided multiple times.');

        $input = new StubInput(['config-file' => ['a', 'b']], ['cache-clear']);
        $this->subject->handle($input, $this->output);
    }
}
