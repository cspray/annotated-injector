<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ComposerJsonScanningThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Bootstrap\VendorPresenceBasedBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Cli\Command\BuildCommand;
use Cspray\AnnotatedContainer\Cli\Command\CacheClearCommand;
use Cspray\AnnotatedContainer\Cli\Command\CommandExecutor;
use Cspray\AnnotatedContainer\Cli\Command\HelpCommand;
use Cspray\AnnotatedContainer\Cli\Command\InitCommand;
use Cspray\AnnotatedContainer\Cli\Command\Service\ConfigFileNameDecider;
use Cspray\AnnotatedContainer\Cli\Command\Service\NullConfigFileNameDecider;
use Cspray\AnnotatedContainer\Cli\Command\ValidateCommand;
use Cspray\AnnotatedContainer\Event\Emitter;

final class AnnotatedContainerCliRunner {

    private function __construct(
        private readonly CommandExecutor $commandExecutor
    ) {


    }

    public static function create(
        ?BootstrappingConfiguration $bootstrappingConfiguration,
        BootstrappingDirectoryResolver $directoryResolver = new VendorPresenceBasedBootstrappingDirectoryResolver(),
        ConfigFileNameDecider $additionalConfigFileNameDecider = new NullConfigFileNameDecider(),
        Emitter $emitter = new Emitter()
    ) : self {
        $thirdPartyInitializer = new ComposerJsonScanningThirdPartyInitializerProvider($directoryResolver);
        $emitter = new Emitter();

    }


    public function commands() : array {
        return $this->commandExecutor->commands();
    }

    public function run(array $argv) : void {

    }

}