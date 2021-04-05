<?php declare(strict_types=1);

namespace Cspray\AnnotatedInjector\Attribute;

use Attribute;

/**
 * Defines a scalar value, that's gathered from an environment variable, that should be used for a param to a Service
 * constructor or method annotated with ServicePrepare.
 *
 * It is possible to pass a scalar's plain value, {@see DefineScalar}. Please also be sure to review the README's
 * documentation for environment variable resolution.
 *
 * @package Cspray\AnnotatedInjector\Attribute
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class DefineScalarFromEnv {

    public function __construct(private string $envVar) {}

}