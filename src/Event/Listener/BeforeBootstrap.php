<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;

interface BeforeBootstrap {

    public function handle(BootstrappingConfiguration $bootstrappingConfiguration) : void;

}
