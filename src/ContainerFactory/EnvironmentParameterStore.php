<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Exception\EnvironmentVarNotFound;
use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;

final class EnvironmentParameterStore implements ParameterStore {

    public function name() : string {
        return 'env';
    }

    public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : string|array|false {
        $value = getenv($key);
        if ($value === false) {
            throw EnvironmentVarNotFound::fromMissingEnvironmentVariable($key);
        }
        return $value;
    }
}
