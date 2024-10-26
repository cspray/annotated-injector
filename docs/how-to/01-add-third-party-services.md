# Adding Third-Party Services

It is very likely that you'll need to add some service to the Container that can't be annotated. AnnotatedContainer offers a set of functions to easily add third-party services with all the feature-parity and functionality available to annotated code. This guide goes through a step-by-step guide on how to integrate the popular [Monolog](https://github.com/Seldaek/monolog) library with [PSR-3](https://www.php-fig.org/psr/psr-3/) services. 

> This guide assumes a basic understanding on how to interact with this library. If you're unsure of something we discuss here it is recommended you checkout the rest of the /docs/tutorials section.

## Step 1 - Install PSR-3 and Monolog

```shell
composer require monolog/monolog psr/log
```

## Step 2 - Create a Service Factory

```php
<?php

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory {

    public function createLogger() : LoggerInterface {
        $log = new Logger('app-name');
        $log->pushHandler(new StreamHandler('php://stdout'));
        
        return $log;
    }

}
```

## Step 3 - Define a DefinitionProvider

```php
<?php

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use function Cspray\AnnotatedContainer\service;
use function Cspray\AnnotatedContainer\serviceDelegate;
use function Cspray\AnnotatedContainer\servicePrepare;
use function Cspray\Typiphy\objectType;

class ThirdPartyServicesProvider implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        $context->addServiceDefinition(service($loggerType = objectType(LoggerInterface::class)));
        $context->addServiceDelegateDefinition(
            serviceDelegate(objectType(MonologLoggerFactory::class), 'createLogger')
        );
        $context->addServicePrepareDefinition(
            servicePrepare(
                objectType(LoggerAwareInterface::class),
                'setLogger'
            )
        );
    }
}

```

## Step 4 - Update Bootstrapping Configuration

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>src</dir>
      <dir>tests</dir>
    </source>
  </scanDirectories>
  <definitionProviders>
    <definitionProvider>ThirdPartyServicesProvider</definitionProvider>
  </definitionProviders>
</annotatedContainer>
```

## Step 5 - Bootstrap your Container

```php
<?php

use Psr\Log\LoggerInterface;
use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Event\Emitter;

$container = Bootstrap::from(new Emitter())->bootstrapContainer();
```

Now, your PSR Logger will be created through a factory. Any services can inject a `LoggerInterface` directly in the constructor, preferred, or implement the `LoggerAwareInterface` to have it injected automatically after construction.