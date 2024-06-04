<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasResolutionReason;
use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface ContainerFactoryEmitter {

    public function emitBeforeContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition) : void;

    public function emitServiceFilteredDueToProfiles(Profiles $profiles, ServiceDefinition $serviceDefinition) : void;

    public function emitServiceShared(Profiles $profiles, ServiceDefinition $serviceDefinition) : void;

    public function emitInjectingMethodParameter(Profiles $profiles, InjectDefinition $injectDefinition) : void;

    public function emitServicePrepared(Profiles $profiles, ServicePrepareDefinition $servicePrepareDefinition) : void;

    public function emitServiceDelegated(Profiles $profiles, ServiceDelegateDefinition $serviceDelegateDefinition) : void;

    public function emitServiceAliasResolution(Profiles $profiles, AliasDefinition $aliasDefinition, AliasResolutionReason $resolutionReason) : void;

    public function emitAfterContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void;
}
