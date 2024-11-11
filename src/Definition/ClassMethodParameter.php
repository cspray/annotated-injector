<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;

interface ClassMethodParameter {

    public function class() : Type;

    /**
     * @return non-empty-string
     */
    public function methodName() : string;

    public function type() : Type|TypeUnion|TypeIntersect;

    /**
     * @return non-empty-string
     */
    public function parameterName() : string;

    public function isStatic() : bool;
}
