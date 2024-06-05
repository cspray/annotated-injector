<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;

final class StubParameterStoreWithDependencies implements ParameterStore {

    public function __construct(private readonly string $prefix) {
    }

    public function name() : string {
        return 'test-store';
    }

    public function fetch(TypeUnion|Type|TypeIntersect $type, string $key) : string {
        return $this->prefix . $key;
    }
}
