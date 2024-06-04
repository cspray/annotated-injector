<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\AfterBootstrap;
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\BeforeBootstrap;

final class StubBootstrapListener implements AfterBootstrap, BeforeBootstrap {

    private array $triggeredEvents = [];

    public function handleAfterBootstrap(BootstrappingConfiguration $bootstrappingConfiguration, ContainerDefinition $containerDefinition, AnnotatedContainer $container, ContainerAnalytics $containerAnalytics,) : void {
        $this->triggeredEvents[] = __METHOD__;
    }

    public function handleBeforeBootstrap(BootstrappingConfiguration $bootstrappingConfiguration) : void {
        $this->triggeredEvents[] = __METHOD__;
    }

    public function getTriggeredEvents() : array {
        return $this->triggeredEvents;
    }
}
