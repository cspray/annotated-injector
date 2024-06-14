# Bootstrap Your Container

As Annotated Container has added more and more functionality the bootstrapping it requires has necessarily grown. It is possible to get up and running without using the provided tooling, but we highly recommend using the CLI tool and corresponding `Cspray\AnnotatedContainer\Bootstrap` to create your Container. This document details how to take advantage of Annotated Container's functionality using this tooling.

## Step 1 - Init Your Configuration

The first step is to create a configuration file that details how Annotated Container should bootstrap itself. As long as you have a `composer.json` in your project's root directory, and it defines at least one directory with a PSR-4 or PSR-0 namespace then the tooling can figure out which directories to scan. Run the following shell command:

```shell
./vendor/bin/annotated-container init
```

If successful you'll get a configuration file named `annotated-container.xml` in the root of your project. In most setups it'll look something like this:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
```

The most important, and the only thing that's actually required, is to define at least 1 source directory to scan. It should be noted that **all** directories autoloaded in your `composer.json` will be scanned, including any `autoload-dev` entries. If this is not desired, be sure to remove these directories after the configuration is generated.

The rest of this guide will add new elements to this configuration. The steps below are optional, if you don't require any "bells & whistles" skip to Step 4.

## Step 2 - Setup Third Party Services (optional)

To define services that can't be annotated you can make use of a `Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider` implementation. Implementing this interface allows you to use the [functional API](../references/03-functional-api.md) to augment your ContainerDefinition without using Attributes. Out-of-the-box, it is expected `DefinitionProvider` implementations will have a zero-argument constructor. Later on in this document I will discuss ways that you can override construction if your implementation has dependencies. Primarily this should be used to integrate third-party libraries that can't have Attributes assigned to them.

Somewhere in your source code:

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;

class ThirdPartyServicesProvider implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        // Make calls to the functional API to add 
    }

}
```

Now, upgrade the configuration to let bootstrapping know which class to use.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
  <definitionProviders>
    <definitionProvider>Acme\Demo\ThirdPartyServicesProvider</definitionProvider>
  </definitionProviders>
</annotatedContainer>
```

### Step 3 - Provide your custom ParameterStore (optional)

In any sufficiently large enough application you'll probably want to take advantage of parameter stores to have complete programmatic control over what non-service values get injected. You can define a list of ParameterStore implementations that should be added during bootstrapping. Out-of-the-box, it is expected that these implementations will have a no argument constructor. Later in this document I'll go over how to override construction of these implementations if they require dependencies.

Somewhere in your source code:

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\Annotatedcontainer\ContainerFactory\ParameterStore;

final class MyCustomParameterStore implements ParameterStore {

    
    public function getName() : string {
        return 'my-store';
    }

    public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
        // do something to fetch the $key and return the value
    }

}
```

Next, update your configuration.


```xml
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
  <parameterStores>
    <parameterStore>Acme\Demo\MyCustomParameterStore</parameterStore>
  </parameterStores>
</annotatedContainer>
```

### Step 4 - Create Your Container

Before completing this step go put some Attributes on the services in your codebase!

Now that the configuration file has been modified, and you've attributed your codebase, to fit your needs you can create your container! If you're using only out-of-the-box functionality this can be done with the following code snippet.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Event\Emitter;

$emitter = new Emitter();

// Add Listeners to respond to events emitted by Annotated Container

$container = Bootstrap::from($emitter)->bootstrapContainer();
```

It is important in this code that you add whatever Listeners might be appropriate to the `$emitter` using `Emitter::addListener`. This includes any Listeners that you might be using from third-party libraries. There is currently no plans to allow Listeners to be defined through configuration and MUST be added as part of your bootstrapping. The Emitter is one of the few pieces of Bootstrapping that is ALWAYS required. It cannot be stressed enough how important setting up your Listeners are in this section of your code.

You have the ability to control specific aspects of the Bootstrapping process by providing different arguments to the `bootstrapContainer` method, using different static constructor methods, or adjust the optional parameters to `Bootstrap::fromMinimalSetup`. 

#### Specifying Profiles

The first argument, `$profiles`, passed to `bootstrapContainer` should be an instance of `Cspray\AnnotatedContainer\Profiles`. This value object has a variety of static constructor methods on it that allow creating an instance with the appropriate values for your use case. If you don't provide any instance of this value object the active profiles will be `['default']`. If you specify your own `Profiles` instance it is HIGHLY RECOMMENDED you included the `default` profile. Otherwise, it is highly expected that your Container will not be wired correctly.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Profiles;

$container = Bootstrap::from(new Emitter())
    ->bootstrapContainer(Profiles::fromList(['default', 'prod']));
```

#### Changing the Configuration File

Perhaps you didn't name your configuration file the default, it is recommended you do so but perhaps there's good reasons to change it. You can pass the second argument, `$configurationFile`, to `bootstrapContainer` that defines the name of the configuration file. If you don't pass any arguments the default value `annotated-container.xml` will be used.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Bootstrap\XmlBootstrappingConfigurationProvider;use Cspray\AnnotatedContainer\Event\Emitter;use Cspray\AnnotatedContainer\Profiles;

$container = Bootstrap::from(new Emitter)
    ->bootstrapContainer(bootstrappingConfigurationProvider: new XmlBootstrappingConfigurationProvider('my-container.xml'));
```

If you require a configuration that is not the default XML files, you can implement your own `Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfigurationProvider` instead.

#### Constructing DefinitionProvider 

There might be dependencies you need to determine what third-party services should be included in your `DefinitionProvider` implementations. If so, there's a `Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory` interface that you can implement and then pass that instance to the `Bootstrap::minimalSetup()` method.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Event\Emitter;

// This method is an implementation provided by the reader
$definitionProviderFactory = MyDefinitionProviderFactory::create();

$container = Bootstrap::from(
    new Emitter(),
    definitionProviderFactory: $definitionProviderFactory 
))->bootstrapContainer();
```

#### Constructing ParameterStore

The custom `ParameterStore` implementations you use might require some dependency to gather the appropriate values. In this case, the `Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory` interface can be implemented and passed to the `$parameterStoreFactory` construct argument.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Event\Emitter;

// This method is an implementation provided by the reader
$parameterStoreFactory = MyParameterStoreFactory::create();

$container = Bootstrap::from(
    new Emitter(),
    parameterStoreFactory: $parameterStoreFactory
))->bootstrapContainer();
```

#### Changing Resolved Paths

By default, boostrapping expects all the path fragments in your configuration to be in the root of your project. You can have explicit control over which absolute path is used by implementing a `Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver`. You'll need to use the `Bootstrap::fromCompleteSetup` and be prepared to provide more dependencies as well. In our code example we use the default, provided implementations, but you can use whatever implementation is appropriate.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;use Cspray\AnnotatedContainer\Bootstrap\DefaultDefinitionProviderFactory;use Cspray\AnnotatedContainer\Bootstrap\DefaultParameterStoreFactory;use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;use Cspray\AnnotatedContainer\Event\Emitter;

// This method is an implementation provided by the reader
$directoryResolver = MyDirectoryResolver::create();

$emitter = new Emitter();

$container = Bootstrap::fromCompleteSetup(
    // if you aren't using php-di replace this with your appropriate implementation
    new PhpDiContainerFactory($emitter),
    $emitter,
    $directoryResolver,
    new DefaultParameterStoreFactory(),
    new DefaultDefinitionProviderFactory()
))->bootstrapContainer();
```

#### Caching ContainerDefinition

The static analysis portion of Annotated Container can, like most static analysis tools, be relatively time-consuming. In PHP applications that act as long-running processes, the type this maintainer tends to develop using Annotated Container, this cost is negligible. It happens just 1 time and is just a small part of the initial startup costs. However, in traditional PHP applications that only live for the length of the request this can be costly. In this situation, it is recommended you configure your bootstrap to cache the ContainerDefinition.

Setting up caching is something that you must explicitly opt into during your bootstrapping. In the 2.x series it was possible to configure a directory to use as a cache. This was removed in 3.0 in favor of a much more robust caching mechanism. The below snippet of code is how to effectively setup your 3.0 Annotated Container to cache similarly to 2.0.

```php
<?php declare(strict_types=1);

namespace Acme\Demo;

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Bootstrap\CacheAwareBootstrappingConfigurationProvider;
use Cspray\AnnotatedContainer\Bootstrap\DefaultDefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefaultParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\XmlBootstrappingConfigurationProvider;use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\Definition\Cache\FileBackedContainerDefinitionCache;use Cspray\AnnotatedContainer\Definition\Serializer\XmlContainerDefinitionSerializer;use Cspray\AnnotatedContainer\Event\Emitter;

$container = Bootstrap::from(new Emitter())
    ->bootstrapContainer(
        bootstrappingConfigurationProvider: new CacheAwareBootstrappingConfigurationProvider(
            new XmlBootstrappingConfigurationProvider(),
            new FileBackedContainerDefinitionCache(
                new XmlContainerDefinitionSerializer(),
                __DIR__ . '/.annotated-container-cache'
            )
        )
    );
```

If the cache implementations provided by Annotated Container are not sufficient, you can create your own `Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache` appropriate for your use case.
