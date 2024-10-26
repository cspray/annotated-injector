<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;

final class StubParameterStore implements ParameterStore {

    public function name() : string {
        return 'test-store';
    }

    public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : string {
        return 'from test-store ' . $key;
    }
}
