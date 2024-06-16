<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ComposerJsonScanningThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Bootstrap\ComposerRuntimePackagesComposerJsonPathProvider;
use Cspray\AnnotatedContainer\Bootstrap\ContainerDefinitionAnalysisOptionsFromBootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\PackagesComposerJsonPathProvider;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Bootstrap\VendorPresenceBasedBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Cli\Command\BuildCommand;
use Cspray\AnnotatedContainer\Cli\Command\CacheClearCommand;
use Cspray\AnnotatedContainer\Cli\Command\Command;
use Cspray\AnnotatedContainer\Cli\Command\CommandExecutor;
use Cspray\AnnotatedContainer\Cli\Command\DisabledCommand;
use Cspray\AnnotatedContainer\Cli\Command\HelpCommand;
use Cspray\AnnotatedContainer\Cli\Command\InitCommand;
use Cspray\AnnotatedContainer\Cli\Command\ValidateCommand;
use Cspray\AnnotatedContainer\Cli\Input\InputParser;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainer\Filesystem\PhpFunctionsFilesystem;

final class AnnotatedContainerCliRunner {

    private function __construct(
        private readonly CommandExecutor $commandExecutor
    ) {
    }

    public static function setup(
        ?BootstrappingConfiguration $configuration,
        BootstrappingDirectoryResolver $directoryResolver = new VendorPresenceBasedBootstrappingDirectoryResolver(),
        ThirdPartyInitializerProvider $thirdPartyInitializerProvider = null,
        Filesystem $filesystem = new PhpFunctionsFilesystem(),
        PackagesComposerJsonPathProvider $composerJsonPathProvider = new ComposerRuntimePackagesComposerJsonPathProvider(),
    ) : self {
        $thirdPartyInitializerProvider ??= new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonPathProvider
        );
        $commandExecutor = new CommandExecutor();
        $commandExecutor->defaultCommand(new HelpCommand($commandExecutor));
        $commandExecutor->addCommand(new InitCommand(
            $directoryResolver,
            $thirdPartyInitializerProvider,
            $filesystem
        ));

        $cache = $configuration?->cache();
        if ($cache === null) {
            $commandExecutor->addCommand(self::disabledCacheRequiredCommand('build'));
            $commandExecutor->addCommand(self::disabledCacheRequiredCommand('cache-clear'));
        } else {
            assert($configuration !== null);
            $analysisOptions = (new ContainerDefinitionAnalysisOptionsFromBootstrappingConfiguration(
                $configuration,
                $directoryResolver
            ))->create();
            $commandExecutor->addCommand(new BuildCommand(
                $cache,
                $analysisOptions
            ));
            $commandExecutor->addCommand(new CacheClearCommand(
                $cache,
                $analysisOptions
            ));
        }

        if ($configuration === null) {
            $commandExecutor->addCommand(self::disabledConfigRequiredCommand('validate'));
        } else {
            $commandExecutor->addCommand(new ValidateCommand($configuration, $directoryResolver));
        }

        return new self($commandExecutor);
    }

    /**
     * @param non-empty-string $commandName
     */
    private static function disabledConfigRequiredCommand(string $commandName) : DisabledCommand {
        $howToEnable = wordwrap(sprintf(
            'A %s object must be provided. This can be accomplished by running the "init" command.',
            BootstrappingConfiguration::class
        ));
        assert($howToEnable !== '');
        return new DisabledCommand($commandName, $howToEnable);
    }

    /**
     * @param non-empty-string $commandName
     */
    private static function disabledCacheRequiredCommand(string $commandName) : DisabledCommand {
        $configClass = BootstrappingConfiguration::class;
        $howToEnable = "A $configClass object with a cache() method that returns a non-null value. For more information, " .
            "read /docs/how-to/03-caching-container-definition.md.";
        $howToEnable = wordwrap($howToEnable);
        assert($howToEnable !== '');
        return new DisabledCommand($commandName, $howToEnable);
    }

    /**
     * @return list<Command>
     */
    public function commands() : array {
        return $this->commandExecutor->commands();
    }

    /**
     * @param list<string> $argv
     */
    public function run(array $argv) : void {
        $this->commandExecutor->execute(
            (new InputParser())->parse($argv),
            new TerminalOutput()
        );
    }
}
