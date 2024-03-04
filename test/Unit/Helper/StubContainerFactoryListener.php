<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\AfterContainerCreation;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\BeforeContainerCreation;
use Cspray\AnnotatedContainer\Profiles;

class StubContainerFactoryListener implements BeforeContainerCreation, AfterContainerCreation {

    private array $triggeredEvents = [];

    public function handleAfterContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $this->triggeredEvents[] = 'AfterContainerCreation';
    }

    public function handleBeforeContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition) : void {
        $this->triggeredEvents[] = 'BeforeContainerCreation';
    }

    public function getTriggeredEvents() : array {
        return $this->triggeredEvents;
    }
}