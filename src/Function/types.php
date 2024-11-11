<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Reflection;

function types() : TypeFactory {
    /** @var ?TypeFactory $typeFactory */
    static $typeFactory = null;
    $typeFactory ??= new TypeFactory();
    return $typeFactory;
}
