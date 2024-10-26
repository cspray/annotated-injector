<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Reflection;

interface TypeUnion extends TypeEqualityComparator {

    /**
     * @return non-empty-string
     */
    public function name() : string;

    /**
     * @return list<Type|TypeIntersect>
     */
    public function types() : array;
}
