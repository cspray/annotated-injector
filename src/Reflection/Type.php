<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Reflection;

interface Type extends TypeEqualityComparator {

    /**
     * @return non-empty-string
     */
    public function name() : string;
}
