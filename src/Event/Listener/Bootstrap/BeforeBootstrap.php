<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Event\Listener;

interface BeforeBootstrap extends Listener {

    public function handleBeforeBootstrap(BootstrappingConfiguration $bootstrappingConfiguration) : void;
}
