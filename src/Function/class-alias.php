<?php

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\RootDirectoryBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\XmlBootstrappingConfiguration;

use Cspray\AnnotatedContainer\Compile\AnnotatedTargetContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Compile\CacheAwareContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Compile\CallableContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionBuilderContext;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionBuilderContextConsumer;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionBuilderContextConsumerFactory;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompileOptions;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompileOptionsBuilder;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompiler;
use Cspray\AnnotatedContainer\Compile\ContainerDefinitionCompilerBuilder;
use Cspray\AnnotatedContainer\Compile\EventEmittingContainerDefinitionCompiler;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactory\EnvironmentParameterStore;
use Cspray\AnnotatedContainer\ContainerFactory\EventEmittingContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStoreFactory;

use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolver;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasResolutionReason;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\StandardAliasDefinitionResolver;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectTargetIdentifier;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinitionBuilder;

use Cspray\AnnotatedContainer\Event\AnnotatedContainerEmitter;
use Cspray\AnnotatedContainer\Event\AnnotatedContainerEvent;
use Cspray\AnnotatedContainer\Event\AnnotatedContainerLifecycle;
use Cspray\AnnotatedContainer\Event\AnnotatedContainerListener;
use Cspray\AnnotatedContainer\Event\ServiceGatheringListener;
use Cspray\AnnotatedContainer\Event\StandardAnnotatedContainerEmitter;

class_alias(Bootstrap::class, 'Cspray\AnnotatedContainer\Bootstrap');
class_alias(BootstrappingConfiguration::class, 'Cspray\AnnotatedContainer\BootstrappingConfiguration');
class_alias(BootstrappingDirectoryResolver::class, 'Cspray\AnnotatedContainer\BootstrappingDirectoryResolver');
class_alias(RootDirectoryBootstrappingDirectoryResolver::class, 'Cspray\AnnotatedContainer\RootDirectoryBootstrappingDirectoryResolver');
class_alias(XmlBootstrappingConfiguration::class, 'Cspray\AnnotatedContainer\XmlBootstrappingConfiguration');

class_alias(AnnotatedTargetContainerDefinitionCompiler::class, 'Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompiler');
class_alias(CacheAwareContainerDefinitionCompiler::class, 'Cspray\AnnotatedContainer\CacheAwareContainerDefinitionCompiler');
class_alias(CallableContainerDefinitionBuilderContextConsumer::class, 'Cspray\AnnotatedContainer\CallableContainerDefinitionBuilderContextConsumer');
class_alias(ContainerDefinitionBuilderContext::class, 'Cspray\AnnotatedContainer\ContainerDefinitionBuilderContext');
class_alias(ContainerDefinitionBuilderContextConsumer::class, 'Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumer');
class_alias(ContainerDefinitionBuilderContextConsumerFactory::class, 'Cspray\AnnotatedContainer\ContainerDefinitionBuilderContextConsumerFactory');
class_alias(ContainerDefinitionCompileOptions::class, 'Cspray\AnnotatedContainer\ContainerDefinitionCompileOptions');
class_alias(ContainerDefinitionCompileOptionsBuilder::class, 'Cspray\AnnotatedContainer\ContainerDefinitionCompileOptionsBuilder');
class_alias(ContainerDefinitionCompiler::class, 'Cspray\AnnotatedContainer\ContainerDefinitionCompiler');
class_alias(ContainerDefinitionCompilerBuilder::class, 'Cspray\AnnotatedContainer\ContainerDefinitionCompilerBuilder');
class_alias(EventEmittingContainerDefinitionCompiler::class, 'Cspray\AnnotatedContainer\EventEmittingContainerDefinitionCompiler');

class_alias(ContainerFactory::class, 'Cspray\AnnotatedContainer\ContainerFactory');
class_alias(ContainerFactoryOptions::class, 'Cspray\AnnotatedContainer\ContainerFactoryOptions');
class_alias(ContainerFactoryOptionsBuilder::class, 'Cspray\AnnotatedContainer\ContainerFactoryOptionsBuilder');
class_alias(EnvironmentParameterStore::class, 'Cspray\AnnotatedContainer\EnvironmentParameterStore');
class_alias(EventEmittingContainerFactory::class, 'Cspray\AnnotatedContainer\EventEmittingContainerFactory');
class_alias(ParameterStore::class, 'Cspray\AnnotatedContainer\ParameterStore');
class_alias(ParameterStoreFactory::class, 'Cspray\AnnotatedContainer\ParameterStoreFactory');

class_alias(AliasDefinitionResolution::class, 'Cspray\AnnotatedContainer\AliasDefinitionResolution');
class_alias(AliasDefinitionResolver::class, 'Cspray\AnnotatedContainer\AliasDefinitionResolver');
class_alias(AliasResolutionReason::class, 'Cspray\AnnotatedContainer\AliasResolutionReason');
class_alias(StandardAliasDefinitionResolver::class, 'Cspray\AnnotatedContainer\StandardAliasDefinitionResolver');

class_alias(AliasDefinition::class, 'Cspray\AnnotatedContainer\AliasDefinition');
class_alias(AliasDefinitionBuilder::class, 'Cspray\AnnotatedContainer\AliasDefinitionBuilder');
class_alias(ConfigurationDefinition::class, 'Cspray\AnnotatedContainer\ConfigurationDefinition');
class_alias(ConfigurationDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ConfigurationDefinitionBuilder');
class_alias(ContainerDefinition::class, 'Cspray\AnnotatedContainer\ContainerDefinition');
class_alias(ContainerDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ContainerDefinitionBuilder');
class_alias(InjectDefinition::class, 'Cspray\AnnotatedContainer\InjectDefinition');
class_alias(InjectDefinitionBuilder::class, 'Cspray\AnnotatedContainer\InjectDefinitionBuilder');
class_alias(InjectTargetIdentifier::class, 'Cspray\AnnotatedContainer\InjectTargetIdentifier');
class_alias(ProfilesAwareContainerDefinition::class, 'Cspray\AnnotatedContainer\ProfilesAwareContainerDefinition');
class_alias(ServiceDefinition::class, 'Cspray\AnnotatedContainer\ServiceDefinition');
class_alias(ServiceDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ServiceDefinitionBuilder');
class_alias(ServiceDelegateDefinition::class, 'Cspray\AnnotatedContainer\ServiceDelegateDefinition');
class_alias(ServiceDelegateDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ServiceDelegateDefinitionBuilder');
class_alias(ServicePrepareDefinition::class, 'Cspray\AnnotatedContainer\ServicePrepareDefinition');
class_alias(ServicePrepareDefinitionBuilder::class, 'Cspray\AnnotatedContainer\ServicePrepareDefinitionBuilder');

class_alias(AnnotatedContainerEmitter::class, 'Cspray\AnnotatedContainer\AnnotatedContainerEmitter');
class_alias(AnnotatedContainerEvent::class, 'Cspray\AnnotatedContainer\AnnotatedContainerEvent');
class_alias(AnnotatedContainerLifecycle::class, 'Cspray\AnnotatedContainer\AnnotatedContainerLifecycle');
class_alias(AnnotatedContainerListener::class, 'Cspray\AnnotatedContainer\AnnotatedContainerListener');
class_alias(ServiceGatheringListener::class, 'Cspray\AnnotatedContainer\ServiceGatheringListener');
class_alias(StandardAnnotatedContainerEmitter::class, 'Cspray\AnnotatedContainer\StandardAnnotatedContainerEmitter');