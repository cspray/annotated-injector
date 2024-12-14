<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Reflection\Type;

interface ClassMethod {

    public function class() : Type;

    /**
     * @return non-empty-string
     */
    public function methodName() : string;

    public function isStatic() : bool;
}
