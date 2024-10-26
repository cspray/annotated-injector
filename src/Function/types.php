<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Reflection;

function types() : TypeFactory {
    static $typeFactory = null;
    $typeFactory ??= new TypeFactory();
    return $typeFactory;
}
