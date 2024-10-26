<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Reflection;

interface TypeIntersect extends TypeEqualityComparator {

    /**
     * @return non-empty-string
     */
    public function name() : string;

    /**
     * @return non-empty-list<Type>
     */
    public function types() : array;
}
