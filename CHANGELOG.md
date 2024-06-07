# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v2.4.0](https://github.com/cspray/annotated-container/tree/v2.4.0) - 2024-06-08

### Added

- Added the ability to inject a collection of services as an array or a custom collection by passing an implementation of `Cspray\AnnotatedContainer\ContainerFactory\ListOf` to an `#[Inject]` attribute.
- Added `Cspray\AnnotatedContainer\ContainerFactory\ListOfAsArray` implementation of to allow implementing a collection of services as an array out-of-the-box 

### Deprecated

Several portions of the library will now trigger deprecation notices. These notices are to indicate you're using a feature that will be removed in 3.0. Each message will explain what feature is being used and what it is being replaced with. Some deprecations can be replaced now while others do not have suitable replacements until 3.0 is launched.

#### Observer Removal Deprecations

In v3 the bootstrapping Observer system is being replaced with a more complete Event system that encompasses the entire Annotated Container lifecycle. The deprecations in this section are related to the removal of this system. There is no suitable replacement until v3 hits for these deprecations. However, transitioning to the new Event system mostly involves implementing a new interface and adjusting a method signature. Your implementations should not require adjustments.

- Providing a `Cspray\AnnotatedContainer\Bootstrap\ObserverFactory` during your bootstrapping process will trigger a deprecation.
- Calling `Cspray\AnnotatedContainer\Bootstrap::addObserver` during your bootstrapping process will trigger a deprecation.
- Extending and using the `Cspray\AnnotatedContainer\Bootstrap\ServiceWiringObserver` will trigger a deprecation.
- Providing any observers in your `annotated-container.xml` file will trigger a deprecation.

#### Transition `ActiveProfiles` to `Profiles`

In v3 the `Cspray\AnnotatedContainer\Profiles` module has been removed and drastically simplified. In v2.x this module was overly complicated and not designed properly for usability in mind. This led to funky code dealing with these complications and a subpar user experience. The entire module was replaced by a single value object and a set of static constructors. There is no suitable replacement until v3 hits for these deprecations.

- Calling `Cspray\AnnotatedContainer\Profiles\ActiveProfiles::getProfiles` or `ActiveProfiles::isActive` will trigger a deprecation.
- Using the `Cspray\AnnotatedContainer\Profiles\ActiveProfilesBuilder` will trigger a deprecation.
- Calling `Cspray\AnnotatedContainer\Profiles\CsvActiveProfilesParser` will trigger a deprecation.

#### Removing Logging

In v3 Logging has been moved to a separate library that has to be explicitly opted in via the new Event system. Making this a separate, explicit opt-in that you must configure greatly simplifies the bootstrapping process and reduces the amount of code in Annotated Container.

- Defining a `<logging></logging>` configuration in `annoatated-container.xml` will trigger a deprecation.

#### Removing Configuration Attribute

The #[Configuration] Attribute has long been deprecated. It was not well-thought-out and the sole reason the ability to inject into properties existed. It has long been recommended to move to using the #[Service] Attribute directly or implementing your own custom attribute.

- Defining an instance of `Cspray\AnnotatedContainer\Attribute\ConfigurationAttribute` will trigger a deprecation.

#### Removing `cacheDir` configuration

In v3 caching functionality is drastically improved and much more control is provided over how your ContainerDefinition is cached. This new caching system must be setup as part of your bootstrapping and can't be configured.

- Defining a `<cacheDir></cacheDir>` configuration in `annotated-container.xml` will trigger a deprecation.

## [v2.3.0](https://github.com/cspray/annotated-container/tree/v2.3.0) - 2024-05-22

### Changed

- Changed static analysis step to no longer throw an error if a ServiceDelegate is encountered without an explicitly
defined Service. Now, a ServiceDefinition will be implicitly added as if the corresponding class was added with all 
default parameters using the functional API.

### Deprecated

- All observers have been deprecated. They will be replaced in 3.0.0. Please see our ADR document for more details.
- All implementations in Cspray\AnnotatedContainer\Profiles have been deprecated. They will be replaced with a single 
value object in 3.0.0. Please see our ADR document for more details.

## [v2.2.0](https://github.com/cspray/annotated-container/tree/v2.2.0) - 2023-05-29

### Added

- A set of `ParameterStoreFactory` implementations to facilitate creating custom `ParameterStore` implementations. See `DelegatedParameterStoreFactory` to use this new functionality.
- A `validate` command that will run a series of checks against your container definition to find potential logical errors. Run `validate --list-constraints` to see what checks are ran.
- Adds support for [illuminate/container](https://github.com/illuminate/container), paving the way for Laravel framework support.

### Changed

- Updated the `AbstractContainerFactory` and implementations to be more consistent with how containers are created and information is logged.

## [v2.1.0](https://github.com/cspray/annotated-container/tree/v2.1.0) - 2023-05-18

### Added

- Added the `cspray/precision-stopwatch` library to facilitate timing how long bootstrapping takes.
- Added a `ContainerAnalyticsObserver` to bootstrapping that is notified with how long it took to create your container.

### Changed

- Updated the default bootstrap logger to output time with microseconds included.
- When a `CompositeDefinitionProvider` is used log more useful information about what implementations are composed.
- Added a `ContainerFactory` parameter to the Bootstrap object to allow using a container library that isn't supported out-of-the-box.

### Fixed

- A more useful error is output when a configuration file is not present instead of a vague libdom error
- When a configuration includes an invalid type display more useful information about which value is invalid

### Deprecated

- Marked the `Configuration`, `ConfigurationAttribute`, `ConfigurationDefinition`, and `ConfigurationDefinitionBuilder` as deprecated, to be removed in v3

## [v2.0.0](https://github.com/cspray/annotated-container/tree/v2.0.0) - 2023-05-12

### Added

- Added the `ocramius/package-versions` package to take care of retrieving Annotated Container version. This ensures a much less error-prone method for determining the package version.

### Removed

- Removed the VERSION file as its functionality was replaced by `ocramius/package-versions`.

## [v2.0.0-rc4](https://github.com/cspray/annotated-container/tree/v2.0.0-rc4) - 2023-03-04

### Added

- Added `cspray/annotated-container-adr` dependency for removed Architectural Decision Records.
- Added `cspray/annotated-container-attribute` dependency for removed Attribute.
- Added `Bootstrap\PreAnalysisObserver`, `Bootstrap\PostAnalysisObserver`, and `Bootstrap\ContainerCreatedObserver`.

### Changed

- Renamed the `Compile` namespace to `StaticAnalysis`, updated interfaces and classes to no longer reference `Compile`.
- Converted the `AnnotatedTargetDefinitionConverter` interface into a concrete implementation.

### Removed

- Removed Architectural Decision Records and provided Attributes, replaced in separate packages.
- Removed `DefaultAnnotatedTargetDefinitionConverter`, this implementation was moved to the interface it implemented.
- Removed the `Bootstrap\Observer` interface. Use the more granular implementations added instead.

## [v2.0.0-rc3](https://github.com/cspray/annotated-container/tree/v2.0.0-rc3) - 2023-02-11

### Changed

- Updated `ThirdPartyInitializerProvider` to read explicit class names from `composer.json` instead of scanning the entire vendor directory which is very resource intensive.

## [v2.0.0-rc2](https://github.com/cspray/annotated-container/tree/v2.0.0-rc2) - 2023-02-11

### Added

- Added a `CompositeDefinitionProvider` to easily compose more than 1 `DefinitionProvider` while still providing a single entry point for Annotated Container.
- Added a `ThirdPartyInitializer` and `ThirdPartyInitializerProvider` to allow libraries integrating with Annotated Container to specify how the XML configuration file should be altered during `./bin/annotated-container init`.
- Added a default `ThirdPartyInitializerProvider` that will scan the `/vendor` directory and include any `ThirdPartyInitializer` configurations that are found.

### Changed

- Updated the XML configuration file to allow specifying more than 1 `DefinitionProvider`. Please note, this only changes the configuration; the actual code still accepts only a single DefinitionProvider.
- Updated the XML configuration file to allow specifying that a set of directories in a vendor package should be scanned.
- Changed the `Observer` interface to allow access to what `ActiveProfiles` are being used for the creation of this container.

### Fixed

- Fixed a bug where the `Bootstrap` had no capacity to accept an `ObserverFactory`, effectively preventing custom Observer creation during the boostrapping process.

## [v2.0.0-rc1](https://github.com/cspray/annotated-container/tree/v2.0.0-rc1) - 2023-01-26

### Fixed

- Fixed a bug where Inject values were not being exported consistently when a ContainerDefinition is serialized.

### Changed

- Updated dependencies to their newest minor versions. Of particular note is upgrading `php-di/php-di` in dev and suggestions to use 7.0 instead of dev version

## [v2.0.0-beta3](https://github.com/cspray/annotated-container/tree/v2.0.0-beta3) - 2022-09-05

### Added

- Added a new `Bootstrap\ServiceGatherer::getServicesWithAttribute` method that allows retrieving all services and definitions that have an Attribute of a given type.

### Fixed

- Fixed a bug where the `Bootstrap\ServiceWiringObserver` was not profile aware and could result in attempting to make a service the Container was unaware of.

## [v2.0.0-beta2](https://github.com/cspray/annotated-container/tree/v2.0.0-beta2) - 2022-09-04

### Changed

- The provided `Bootstrap\ServiceWiringObserver` now provides an array of `Bootstrap\ServiceFromServiceDefinition` which provides the `Definition\ServiceDefinition` as well as the corresponding service. This provides access to the new `Definition\ServiceDefinition::getAttribute` method which could be useful in contexts where service wiring occurs.

## [v2.0.0-beta1](https://github.com/cspray/annotated-container/tree/v2.0.0-beta1) - 2022-09-04

Version 2 represents significant improvements but includes backwards-breaking changes. If you run into problems migrating from v1 to v2 please [submit an Issue](https://github.com/cspray/annotated-container/issues/new).

### Added

- A simplified, unified interface for interacting with Annotated Container during bootstrapping. Implement a `Boostrap\Observer` to respond to the various Annotated Container lifecycle events.
- Ability to setup a bootstrapping observer through the `annotated-container.xml` configuration file.
- Added the ability to retrieve the Attribute instance for a given Definition.

### Changed

- The codebase was restructured to be separated by namespaces more properly. Attribute namespaces were not changed, the biggest user-facing migration change will be updating class names using in bootstrapping code.
- The `Serializer\ContainerDefinitionSerializer` interface has been refactored into a concrete instance that uses an XML serialization format.
- Improves handling `Attribute\Inject` serialization to properly handle objects being able to be used in Attribute constructors.
- Exceptions were renamed to properly convey information about what went wrong.

### Removed

- Removed code constructs that were deprecated in Version 1. A complete list of removed constructs:
  - `Definition\ContainerDefinition::merge`
  - `Definition\ServiceDefinition::equals`
- Removed the `JsonContainerDefinitionSerializer` implementation, all serialization happens with the `Serializer\ContainerDefinitionSerializer`.
- Removed the entire bootstrapping event system. It had different, overly-complicated pieces. The system's functionality was replaced with the new bootstrap observer system.
- Removed several functions aimed at bootstrapping a container. Supported bootstrapping happens through the `Bootstrap\Bootstrap` instance. A complete list of removed functions:
  - `Cspray\AnnotatedContainer\compiler`
  - `Cspray\AnnotatedContainer\containerFactory`
  - `Cspray\AnnotatedContainer\eventEmitter`

### Fixed

- Bug where the `AutowireableInvoker` is not properly aliased to the Container implementation.
- Bug where running `bin/annotated-container init` without a `composer.json` results in an error accessing a file that does not exist.

## [v1.6.0](https://github.com/cspray/annotated-container/tree/v.1.6.0) - 2022-08-20

This release only deprecates code constructs replaced in v2.

### Changed

- Deprecated `AnnotatedContainerEmitter`
- Deprecated `AnnotatedContainerEvent`
- Deprecated `AnnotatedContainerLifecycle`
- Deprecated `AnnotatedContainerListener`
- Deprecated `EventEmittingContainerDefinitionCompiler`
- Deprecated `EventEmittingContainerFactory`
- Deprecated `JsonContainerDefinitionSerializer`
- Deprecated `ServiceGatheringListener`
- Deprecated `StandardAnnotatedContainerEmitter`
- Deprecated `SupportedContainer`
- Deprecated function `eventEmitter`
- Deprecated function `compiler`
- Deprecated function `containerFactory`

## [v1.5.2](https://github.com/cspray/annoated-container/tree/v1.5.2) - 2022-08-13

### Added

- Added a `ServiceGatherListener` that encapsulates gathering a collection of services from the Container matching a given type. One of the primary use cases is to prepare a service that needs a collection of other services. For example, adding Controllers to a HTTP Routing system.

## [v1.5.1](https://github.com/cspray/annotated-container/tree/v1.5.1) - 2022-08-13

### Fixed

- Fixed a bug where serializing an Inject value that includes an array of scalars would result in an unexpected exception trying to parse type information.

## [v.1.5.0](https://github.com/cspray/annoated-container/tree/v1.5.0) - 2022-08-12

### Added

- Added ability to implement custom Attributes to configure Annotated Container.

### Changed

- Updated compilation logs to display the precise Attribute found.

## [v.1.4.0](https://github.com/cspray/annotated-container/tree/v1.4.0) - 2022-08-11

### Added

- Added extensive logging to all compiler and container factory operations.
- Added ability to define a stdout or file logger when using the Bootstrap functionality.
- Added ability to define a set of profiles that should be excluded from logging when using the Bootstrap functionality.

### Fixed

- Ensure that abstract services marked for delegation are not improperly aliased.
- Provides an appropriate resolution reason if multiple concrete services are marked primary.

## [v1.3.0](https://github.com/cspray/annotated-container/tree/v1.3.0) - 2022-08-06

### Added 

- Added an event system for programmatic access to the ContainerDefinition and Container before and after each is created.

### Changed

- Updated the parsing of the #[ServiceDelegate] attribute to implicitly determine the service to create off of the method return type if no argument is passed to the Attribute.

### Fixed

- Fixed an error where building a container from a cached definition was not respecting an enum or an array of enums as a value for `#[Inject]`.

## [v.1.2.1](https://github.com/cspray/annotated-container/tree/v1.2.1) - 2022-08-01

### Fixed

- Fixed an error where the default directory resolver used by the Bootstrap was referencing an incorrect directory.
- Fixed an error where ConfigurationDefinition were not properly included in the JSON Serializer.

## [v1.2.0](https://github.com/cspray/annotated-container/tree/v1.2.0) - 2022-07-24

### Added

- Added a `ProfileAwareContainerDefinition` that decorates a `ConatinerDefinition` and will only return entries that have an active profile.
- Added `AliasDefinitionResolver` that can be used by ContainerFactory implementations and other parts of Annotated Container to determine the concrete alias assignable to an abstract service.
- Added `AbstractContainerFactory` that de-duplicates some small aspects of implementing a ContainerFactory.
- Added `AnnotatedContainerVersion` object to easily get access to what version of Annotated Container is installed.

### Changed

- Both the `AurynContainerFactory` and `PhpDiContainerFactory` extends new `AbstractContainerFactory`.

## [v1.1.0](https://github.com/cspray/annotated-container/tree/v1.1.0) - 2022-07-22

### Added

- Added cspray/architectural-decision to dependencies. Implemented first Architectural Decision Record explaining why only 1 `ContainerDefinitionBuilderContextConsumer` is allowed.
- Added vimeo/psalm to dev dependencies, along with running static analysis checks up to reporting level 2. Getting to reporting level 1 will take significantly more effort.
- Added interfaces and implementations for creating a configuration to define how to create your Container and to create a Container based on that configuration.
- Added a `SupportedContainers` enum that lists the implementations supported out-of-the-box.
- Added a CLI tool to create a configuration, build a ContainerDefinition, and clear any cache.
- Added a `Bootstrap` object that allows for easily creating a Container based on a configuration file generated by the CLI tool.

### Changed

- Changed the `containerFactory()` to allow choosing which Container to use by passing a `SupportedContainers` enum value. You can still pass no arguments to receive the "default" Container.
- Changed the `containerFactory()` to always return the same `ContainerFactory` instance on successive calls.

## [1.0.2](https://github.com/cspray/annotated-container/tree/v1.0.2) - 2022-07-06

### Fixed

- Fixed an oversight where Service properties were not marked as readonly
- Fixed a bug where caching a ContainerDefinition was not including the InjectDefinitions causing any Container to be created from the cached results to be invalid if an Inject Attribute is used.

## [1.0.1](https://github.com/cspray/annotated-container/tree/v1.0.1) - 2022-07-05

### Changed

- Updated README and composer suggest to specify that PHP-DI requires the v7.x-dev branch.

## [1.0.0](https://github.com/cspray/annotated-container/tree/v1.0.0) - 2022-06-26

### Added

- Added the missing `injectMethodParam` and `injectProperty` methods for the functional API equivalent of the `#[Inject]` Attribute.

### Fixed

- Fixed a bug where the `CacheAwareContainerDefinitionCompiler` would not recognize a similar set of directories that were provided in a different order.
- Fixed a bug where services with explicit profiles were always being shared as services although their profile might not be in the list of active profiles.
- Fixed a bug where Inject and Service definitions were not properly including the implicit 'default' profile.

## [0.6.0](https://github.com/cspray/annotated-container/tree/v0.6.0) - 2022-06-25

### Added

- Added ability to invoke a callable that's capable of recursively autowiring parameters called, `AutowireableInvoker`.
- Added an implicitly shared service, `ActiveProfiles`, that provides information about what profiles were marked as active for the creation of this `Container`.
- Added an `AnnotatedContainer` interface that defines the granular interfaces implemented by the Container returned from a `ContainerFactory`.
- Added the `cspray/annotated-target` package which replaces the functionality from our own Annotated Target implementations.

### Removed

- Removed the ability to mark a `#[Service]` as shared or not. All services are shared by default, and you cannot "unshare" a service. This functionality has a lot of odd behavior around it and other mechanisms should be used to gain this functionality.
- Removed the `AnnotatedTarget`, `AnnotatatedTargetParser`, and `StaticAnalysisAnnotatedTargetParser`. 

### Changed

- The return type of a `ContainerFactory::createContainer` is now an `AnnotatedContainer` instead of the previous type intersect. For userland code this should not require any changes, only `ContainerFactory` implementations should need to be changed.

### Fixed

- Fixed a bug where a `TypeInteresect` could be passed to a ParameterStore resulting in a `TypeError`.
- Fixed the way implicit profiles are handled within Service and Inject definitions.

## [0.5.0](https://github.com/cspray/annotated-container/tree/v.0.5.0) - 2022-06-04

### Added

- A new `fixture_src/` directory that stores example source code used for the automated test suite.
- Several improvements to the way that Fixtures are handled in the test suite such that the code examples in `fixture_src/` have a first-class representation in the test suite through the `Cspray\AnnotatedContainerFixture\Fixtures` class.
- Implementations for 

### Changed

- Improved many test cases, especially testing definition creation, to be more thorough and improve the test:assertion ratio.

### Fixed

- A bug where fetching a named service by name and then by type would result in non-shared instances.
- A bug where injecting services by name would not work appropriately.

### Removed

- Removed reliance on the `cspray/annotated-container-dummy-apps` package. All code required for the test suite is located in the newly added `fixture_src/` directory.

## [0.4.0](https://github.com/cspray/annotated-container/tree/v0.4.0) - 2022-05-08

> This version introduces many backwards breaking changes. This release also represents a significant step towards a stable API. This level of BC break is not expected in future versions.

### Added

- Added the `cspray/typiphy` package.
- Added a new `#[Inject]` Attribute with the flexibility to handle use cases from previous Inject* Attributes.
- Added a `ParameterStore` that allows injecting arbitrary values at runtime with `#[Inject]`.
- Added an `AutowireableFactory` interface that allows for creating arbitrary objects and defining parameters that cannot be autowired. This interface is intended to allow for the autowire object construction without depending on a Container directly.
- Added new `compiler()` and `containerFactory()` functions for easily interacting with the library in the most common scenarios.
- Added a new `#[Configuration]` Attribute that allows properties to have arbitrary values populated with `#[Inject]`.
- Added a new `PhpDiContainerFactory` for creating a Container with PHP-DI. This class is only available if php-di/php-di is explicitly installed.

### Changed

- Changed many definitions to no longer require other definitions, instead relying on an `ObjectType` from the Typiphy package. This allows for greater flexibility in creating ContainerDefinition and makes it more clear that we're really requiring an ObjectType in a lot of places, not necessarily the entire ServiceDefinition.
- Moved the `AurynContainerFactory` into the `Cspray\AnnotatedContainer\ContainerFactory` namespace. This class is now only available if rdlowrey/auryn is explicitly installed.

### Removed

- Many internal classes were removed to simplify static parsing process. The static parsing is now largely identifying which classes, methods, or parameters to target for reflection when the container is created.
- Removed the `#[InjectScalar]`, `#[InjectService]`, and `#[InjectEnv]`. All uses can now be covered by the new `#[Inject]` Attribute.

## [0.3.0](https://github.com/cspray/annotated-container/tree/v0.3.0) - 2022-04-16

### Added

- Adds ability to mark a Service as primary. If multiple aliases are found the one marked as primary will be used by default.
- Add ability to define multiple InjectScalar and InjectEnv attributes and to specify a profile, or profiles, that each annotation belongs to.
- Add ability to name a Service by passing an argument and allow retrieval with an arbitrary value without having to use the type as an identifier.
- Add ability to define a Service that gets recreated every time you get it from the Container.

### Changed

- Made several changes to the internal parsing of the codebase. This shouldn't have any changes in the public-facing API. In theory these improvements should increase the performance and memory efficiency when parsing. The primary intent of the changes was to make parsing easier to follow and reason about.
- The ContainerDefinitionCompiler is no longer aware of active profiles and will provide a ContainerDefinition with all parsed annotations. It is the responsibility of the ContainerFactory to parse out information relating to active profiles. This change was necessary to accommodate profiles as arguments, including profiles defined as constants.

### Fixed

- An error in the README documentation referencing an incorrect variable.
- Directory paths in all tests point to new directory structure.
- A dev-only dependency, `mikey179/vfsStream` was inadvertently included in the `require` section. This dependency is now properly a `require-dev` dependency.
- Arguments passed to Attributes better differentiates between compile and runtime values by introducing an AnnotationValue. Many 

### Removed

- Extracted the Symfony Console tool into its own repo, `cspray/annotated-container-cli`. Removed the dependency on `symfony/console`.
- Extracted the code examples used in unit tests to its own repo, `cspray/annotated-container-dummy-apps`.
- Removed the `#[ServiceProfile]` Attribute, you can now define profiles for a Service directly on the `#[Service]` annotation by passing a profiles argument.

## [0.2.0](https://github.com/cspray/annotated-container/tree/v0.2.0) - 2022-03-22

### Added

- Support for creating a PSR-11 Container
- Allow to define multiple active profiles at compile time
- Allow defining multiple profiles that a service belongs to
- Improved the way many Definition objects are built
- Allow merging multiple ContainerDefinitions
- Allow a ContainerDefinition to be serialized and deserialized
- Introduced a first-pass CLI tool for compiling a ContainerDefinition and caching it to file
- Allow for using a factory to create a service

### Changed

- Renamed many classes, properties, and methods to reflect `Container` instead of `Injector`
- Renamed `UseScalar` -> `InjectScalar`
- Renamed `UseScalarFromEnv` -> `InjectEnv`
- Rename `UseService`-> `InjectService`

## [0.1.0](https://github.com/cspray/annotated-container/tree/v0.1.0) - 2021-04-06

### Added

- `Service` Attribute to define an interface or class as a shared service or alias.
- `ServicePrepare` Attribute to define a method to be invoked after object creation.
- `UseService` Attribute to define a parameter on a `Service` constructor or `ServicePrepare` method to use the
  provided type to resolve the dependency.
- `UseScalar` Attribute to define a hardcoded, non-object value on a `Service` constructor or `ServicePrepare`
  method.
- `UseScalarFromEnv` Attribute to define a hardcoded, non-object value to be determined by an environment variable
  on a `Service` constructor or `ServicePrepare` method.
- `InjectorDefinitionCompiler` to turn annotated PHP source code in a directory into an `InjectorDefinition` which defines how to construct
  the corresponding `Injector`. An implementation using PHP-Parser is also provided.
- `InjectorFactory` to take an `InjectorDefinition` and turn it into a DI container. An implementation that 
  wires an Auryn `Injector` is also provided.


