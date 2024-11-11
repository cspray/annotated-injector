# AnnotatedContainer

[![Unit Tests](https://github.com/cspray/annotated-container/actions/workflows/php.yml/badge.svg)](https://github.com/cspray/annotated-container/actions/workflows/php.yml)

A Dependency Injection framework for creating an autowired, feature-rich, [PSR-11](https://www.php-fig.org/psr/psr-11/) compatible Container using PHP 8 Attributes!

- Designate an interface as a Service and easily configure which concrete implementations to use
- Delegate service construction to a factory
- Inject scalar values, environment variables, and other services into your constructors and setters
- Automatically invoke methods after the service is constructed
- Use Profiles to easily use different services in different runtimes
- Create type-safe, highly flexible configuration objects
- Easily include third-party services that cannot be easily annotated
- Support for a variety of different Dependency Injection containers

## Quick Start

This quick start is intended to get you familiar with Annotated Container's core functionality and get a working Container created. First, a simple example showing how an interface can be aliased to a concrete Service. After that we'll show you how to get a Container to create the Service.

### Code Example

```php
<?php declare(strict_types=1);

// interfaces and classes in __DIR__ . '/src'

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\InjectEnv;

#[Service]
interface BlobStorage {

    public function store(string $identifier, string $contents) : void;
    
    public function retrieve(string $identifier) : ?string;

}

#[Service]
class FilesystemStorage implements BlobStorage {
    
    public function store(string $identifier, string $contents) : void {
        file_put_contents($identifier, $contents);
    }
    
    public function retrieve(string $identifier) : ?string {
        return file_get_contents($identifier) ?? null;
    }

}
```

This example is built upon in the docs. Check out the tutorials for more examples of Annotated Container's functionality!

### Bootstrapping Your Container

Annotated Container ships with a built-in CLI tool to easily create a configuration detailing how to build your Container and a corresponding `Cspray\AnnotatedContainer\Bootstrap\Bootstrap` implementation to create your Container using that configuration. It is highly recommended to use the provided tooling to create your Container.

> The CLI tool offers extensive documentation detailing how to run commands and what options are available. If you're ever looking for more info run: `./vendor/bin/annotated-container help`

The first step is to create the configuration. By default, the tooling will look at your `composer.json` to determine what directories to scan and create a directory that the ContainerDefinition can be cached in. Run the following command to complete this step. 

```
./vendor/bin/annotated-container init
```

The configuration file will be created in the root of your project named "annotated-container.xml". The cache directory will also be created in the root of your project named ".annotated-container-cache". Check out the command's help documentation for available options, including how to customize these values.

Be sure to review the generated configuration! A "normal" Composer setup might result in a configuration that looks like the following. If there are any directories that should be scanned but aren't listed in `<source></source>` be sure to include them. Conversely, if there are directories included that _shouldn't_ be scanned be sure to remove them.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="3.0">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
</annotatedContainer>
```

Now, bootstrap your Container in your app. In this example, we're going to assume that you 
have `php-di/php-di` installed.

```php
<?php declare(strict_types=1);

// app bootstrap in __DIR__ . '/app.php'
require __DIR__ . '/vendor/autoload.php';

use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;

$emitter = new Emitter();

// Add whatever Listeners are required for your application

// Include other active profiles in this list
// If the only active profile is default you can call this method without any arguments
$container = Bootstrap::fromAnnotatedContainerConventions(
    new PhpDiContainerFactory($emitter), $emitter
)->bootstrapContainer(Profiles::fromList(['default']));

$storage = $container->get(BlobStorage::class);     // instanceof FilesystemStorage
```

## Installation

```
composer require cspray/annotated-container
```

### Choose a Backing Container

AnnotatedContainer does not provide any of the actual Container functionality. We provide Attributes and definition objects that can determine how actual implementations are intended to be setup. AnnotatedContainer currently supports the following backing Containers:

#### [rdlowrey/auryn](https://github.com/rdlowrey/auryn)

```
composer require rdlowrey/auryn
```

#### [php-di/php-di](https://github.com/php-di/php-di)

```
composer require php-di/php-di
```

#### [illuminate/container](https://github.com/illuminate/container)

```
composer require illuminate/container
```

## Documentation

This library is thoroughly documented in-repo under the `/docs` directory. The documentation is split into three parts; Tutorials, How Tos, and References.

**Tutorials** are where you'll want to start. It'll expand on the examples in the "Quick Start" and teach you how to do most of the things you'll want to do with the library. This documentation tends to split the difference between the amount of code and the amount of explanation.

**How Tos** are where you'll go to get step-by-step guides on how to achieve specific functionality. These documents tend to be more code and less explanation. We assume you've gotten an understanding of the library and have questions on how to do something beyond the "normal" use cases. 

**References** are where you can get into the real internal, technical workings of the library. List of APIs and more technically-explicit documentation can be found here. References may be a lot of code, a lot of explanation, or a split between the two depending on the context.

## Roadmap

The Roadmap can be found in the [annotated-container Project page](https://github.com/users/cspray/projects/1/views/1).

## External Resources

### Notable Libraries Using Annotated Container

- [Annotated Console](https://github.com/cspray/annotated-console)
- [Labrador Async Event](https://github.com/labrador-kennel/async-event) (3.0.0-beta2+)

### Blog Posts

- [Introducing Annotated Container - Part 1](https://www.cspray.io/blog/introducing-annotated-container-part-1/)
- [Introducing Annotated Container - Part 2](https://www.cspray.io/blog/introducing-annotated-container-part-2/)
- [Introducing Annotated Container - Part 3](https://www.cspray.io/blog/introducing-annotated-container-part-3/)
- [Annotated Container, Why Attributes?](https://www.cspray.io/blog/annotated-container-why-attributes/)
- [Autowiring Annotated Container](https://www.cspray.io/blog/autowiring-and-annotated-container/)

### Demos

- [cspray/annotated-container-microframework](https://github.com/cspray/annotated-container-microframework)
