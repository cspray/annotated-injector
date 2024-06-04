<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

interface BootstrapEmitter {

    public function emitBeforeBootstrap(BootstrappingConfiguration $bootstrappingConfiguration) : void;

    public function emitAfterBootstrap(
        BootstrappingConfiguration $bootstrappingConfiguration,
        ContainerDefinition $containerDefinition,
        AnnotatedContainer $container,
        ContainerAnalytics $containerAnalytics,
    ) : void;
}
