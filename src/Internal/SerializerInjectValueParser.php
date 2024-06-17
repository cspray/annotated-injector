<?php

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\boolType;
use function Cspray\Typiphy\callableType;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\iterableType;
use function Cspray\Typiphy\mixedType;
use function Cspray\Typiphy\nullType;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;
use function Cspray\Typiphy\typeIntersect;
use function Cspray\Typiphy\typeUnion;
use function Cspray\Typiphy\voidType;

/**
 * @Internal
 */
final class SerializerInjectValueParser {

    /**
     * @param non-empty-string|class-string $rawType
     * @return Type|TypeUnion|TypeIntersect
     */
    public function convertStringToType(string $rawType) : Type|TypeUnion|TypeIntersect {
        if (str_contains($rawType, '|')) {
            $types = [];
            foreach (explode('|', $rawType) as $unionType) {
                assert($unionType !== '');
                $types[] = $this->convertStringToType($unionType);
            }
            /** @psalm-var non-empty-list<Type> $types */
            $type = typeUnion(...$types);
        } elseif (str_contains($rawType, '&')) {
            $types = [];
            foreach (explode('&', $rawType) as $intersectType) {
                assert(class_exists($intersectType));
                $parsedType = $this->convertStringToType($intersectType);
                assert($parsedType instanceof ObjectType);
                $types[] = $parsedType;
            }
            $type = typeIntersect(...$types);
        } else {
            $type = match ($rawType) {
                'string' => stringType(),
                'int', 'integer' => intType(),
                'float', 'double' => floatType(),
                'bool', 'boolean' => boolType(),
                'array' => arrayType(),
                'mixed' => mixedType(),
                'iterable' => iterableType(),
                'null', 'NULL' => nullType(),
                'void' => voidType(),
                'callable' => callableType(),
                default => null
            };

            if ($type === null) {
                assert(class_exists($rawType));
                $type = objectType($rawType);
            }
        }

        return $type;
    }
}
