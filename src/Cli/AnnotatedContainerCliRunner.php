<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Bootstrap\VendorPresenceBasedBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Cli\Command\CommandExecutor;

final class AnnotatedContainerCliRunner {

    private function __construct(
        private readonly CommandExecutor $commandExecutor
    ) {


    }

    public static function setup(
        ?BootstrappingConfiguration $configuration,
        BootstrappingDirectoryResolver $directoryResolver = new VendorPresenceBasedBootstrappingDirectoryResolver(),
        ThirdPartyInitializerProvider $thirdPartyInitializerProvider = null
    ) : self {



    }

    public function commands() : array {
        return $this->commandExecutor->commands();
    }

    public function run(array $argv) : void {

    }

}
