<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Reflection;

interface TypeEqualityComparator {

    public function equals(Type|TypeUnion|TypeIntersect $type) : bool;
}
