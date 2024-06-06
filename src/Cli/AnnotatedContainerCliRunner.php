<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfigurationProvider;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ComposerJsonScanningThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Bootstrap\DefaultDefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefaultParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\VendorPresenceBasedBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Cli\Command\BuildCommand;
use Cspray\AnnotatedContainer\Cli\Command\CacheClearCommand;
use Cspray\AnnotatedContainer\Cli\Command\HelpCommand;
use Cspray\AnnotatedContainer\Cli\Command\InitCommand;
use Cspray\AnnotatedContainer\Cli\Command\Service\ComposerExtraConfigBasedConfigFileNameDecider;
use Cspray\AnnotatedContainer\Cli\Command\Service\ConfigFileNameDecider;
use Cspray\AnnotatedContainer\Cli\Command\Service\NullConfigFileNameDecider;
use Cspray\AnnotatedContainer\Cli\Command\ValidateCommand;
use Cspray\AnnotatedContainer\Event\Emitter;

final class AnnotatedContainerCliRunner {

    private function __construct(
        private readonly CommandExecutor $commandExecutor
    ) {


    }

    public static function fromMinimalSetup(
        ?BootstrappingConfiguration $bootstrappingConfiguration,
        BootstrappingDirectoryResolver $directoryResolver = new VendorPresenceBasedBootstrappingDirectoryResolver(),
        ConfigFileNameDecider $additionalConfigFileNameDecider = new NullConfigFileNameDecider()
    ) : self {
        $thirdPartyInitializer = new ComposerJsonScanningThirdPartyInitializerProvider($directoryResolver);
        $emitter = new Emitter();

        $init = new InitCommand($directoryResolver, $thirdPartyInitializer);
        $build = new BuildCommand(
            $bootstrappingConfigurationProvider,
            $directoryResolver,
            $emitter,
            $additionalConfigFileNameDecider
        );
        $cache = new CacheClearCommand($directoryResolver);
        $validate = new ValidateCommand($directoryResolver);
    }

    public static function fromCompleteSetup(
        InitCommand $initCommand,
        BuildCommand $buildCommand,
        CacheClearCommand $cacheClearCommand,
        ValidateCommand $validateCommand
    ) : self {

        $commandExecutor = new CommandExecutor();

        $commandExecutor->defaultCommand(new HelpCommand($commandExecutor));
    }

    public function run(array $argv) : void {

    }

}