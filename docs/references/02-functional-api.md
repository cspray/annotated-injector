# Functional API

Annotated Container provides a series of functions that are designed to:

- Provide a mechanism for defining services that can't be annotated
- Define a standardized way for passing arguments to `AutowireableFactory` and `AutowireableInvoker`.

This document lists the functions for each purpose.

## Defining Services

```php
\Cspray\AnnotatedContainer\service(
    \Cspray\Typiphy\ObjectType $service,
    ?string $name = null,
    array $profiles = [],
    bool $isPrimary = false
) : \Cspray\AnnotatedContainer\Definition\ServiceDefinition;

\Cspray\AnnotatedContainer\alias(
    \Cspray\Typiphy\ObjectType $abstract,
    \Cspray\Typiphy\ObjectType $concrete
) : \Cspray\AnnotatedContainer\Definition\AliasDefinition;

\Cspray\AnnotatedContainer\serviceDelegate(
    \Cspray\Typiphy\ObjectType $factoryClass,
    string $factoryMethod
) : \Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;

\Cspray\AnnotatedContainer\servicePrepare(
    \Cspray\Typiphy\ObjectType $service,
    string $method
) : \Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;

\Cspray\AnnotatedContainer\inject(
    \Cspray\Typiphy\ObjectType $service,
    string $method,
    string $paramName,
    \Cspray\Typiphy\Type|\Cspray\Typiphy\TypeUnion|\Cspray\Typiphy\TypeIntersect $type,
    mixed $value,
    array $profiles = [],
    string $from = null
) : \Cspray\AnnotatedContainer\Definition\InjectDefinition;

```

## Autowireable Parameters

```php
\Cspray\AnnotatedContainer\autowiredParams(
    \Cspray\AnnotatedContainer\AutowireableParameter... $parameters
) : \Cspray\AnnotatedContainer\AutowireableParameterSet;

\Cspray\AnnotatedContainer\serviceParam(
    string $name,
    \Cspray\Typiphy\ObjectType $service
) : \Cspray\AnnotatedContainer\AutowireableParameter;

\Cspray\AnnotatedContainer\rawParam(
    string $name,
    mixed $value
) : \Cspray\AnnotatedContainer\AutowireableParameter;
```
