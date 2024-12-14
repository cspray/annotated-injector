<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Reflection;

use Cspray\AnnotatedContainer\Exception\UnknownReflectionType;
use ReflectionNamedType;
use ReflectionType;

final class TypeFactory {

    public function fromReflection(?ReflectionType $reflectionType) : Type|TypeUnion|TypeIntersect {
        if ($reflectionType === null) {
            return $this->mixed();
        } elseif ($reflectionType instanceof ReflectionNamedType) {
            $name = $reflectionType->getName();
            $type = $this->fromName($name);

            if ($reflectionType->allowsNull() && $type !== $this->mixed() && $type !== $this->null()) {
                $type = $this->union(
                    $this->null(),
                    $type
                );
            }
        } elseif ($reflectionType instanceof \ReflectionUnionType) {
            $unionTypes = [];
            foreach ($reflectionType->getTypes() as $rt) {
                $unionTypes[] = $this->fromReflection($rt);
            }
            $type = $this->union(...$unionTypes);
        } elseif ($reflectionType instanceof \ReflectionIntersectionType) {
            $intersectTypes = [];
            foreach ($reflectionType->getTypes() as $rt) {
                $intersectTypes[] = $this->fromReflection($rt);
            }
            $type = $this->intersect(...$intersectTypes);
        } else {
            throw UnknownReflectionType::fromReflectionTypeInvalid();
        }

        return $type;
    }

    public function fromName(string $name) : Type {
        $type = match ($name) {
            'array' => $this->array(),
            'bool' => $this->bool(),
            'float' => $this->float(),
            'int' => $this->int(),
            'mixed' => $this->mixed(),
            'never' => $this->never(),
            'null' => $this->null(),
            'object' => $this->object(),
            'self' => $this->self(),
            'static' => $this->static(),
            'string' => $this->string(),
            'void' => $this->void(),
            default => null,
        };
        if ($type === null) {
            assert(class_exists($name));
            $type = $this->class($name);
        }

        return $type;
    }

    public function array() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('array');
    }

    public function bool() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('bool');
    }

    /**
     * @param class-string $class
     * @return Type
     */
    public function class(string $class) : Type {
        /** @var array<class-string, Type> $types */
        static $types = [];
        return $types[$class] ??= $this->createType($class);
    }

    public function float() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('float');
    }

    public function int() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('int');
    }

    public function mixed() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('mixed');
    }

    public function never() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('never');
    }

    public function null() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('null');
    }

    public function object() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('object');
    }

    public function self() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('self');
    }

    public function static() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('static');
    }

    public function string() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('string');
    }

    public function void() : Type {
        /** @var ?Type $type */
        static $type;
        return $type ??= $this->createType('void');
    }

    public function union(Type|TypeIntersect $first, Type|TypeIntersect $second, Type|TypeIntersect...$additional) : TypeUnion {
        return $this->createTypeUnion(
            $first,
            $second,
            ...$additional
        );
    }

    public function nullable(Type $type) : TypeUnion {
        return $this->createTypeUnion(
            $this->null(),
            $type
        );
    }

    public function intersect(Type $first, Type $second, Type...$additional) : TypeIntersect {
        return $this->createTypeIntersect(
            $first,
            $second,
            ...$additional
        );
    }

    /**
     * @param non-empty-string $typeName
     * @return Type
     */
    private function createType(string $typeName) : Type {
        return new class($typeName) implements Type {
            public function __construct(
                /**
                 * @var non-empty-string
                 */
                private readonly string $name
            ) {
            }

            public function name() : string {
                return $this->name;
            }

            public function equals(TypeUnion|TypeIntersect|Type $type) : bool {
                if (!$type instanceof Type) {
                    return false;
                }

                return $type->name() === $this->name();
            }
        };
    }

    private function createTypeUnion(Type|TypeIntersect $one, Type|TypeIntersect $two, Type|TypeIntersect...$additional) : TypeUnion {
        return new class(array_values([$one, $two, ...$additional])) implements TypeUnion {
            /**
             * @var non-empty-string
             */
            private readonly string $name;
            public function __construct(
                /**
                 * @var non-empty-list<Type|TypeIntersect>
                 */
                private readonly array $types
            ) {
                $typeName = static fn(Type|TypeIntersect $type): string =>
                    $type instanceof Type ? $type->name() : sprintf('(%s)', $type->name());
                $this->name = join('|', array_map($typeName, $this->types));
            }

            public function name() : string {
                return $this->name;
            }

            /**
             * @return non-empty-list<Type|TypeIntersect>
             */
            public function types() : array {
                return $this->types;
            }

            public function equals(TypeUnion|TypeIntersect|Type $type) : bool {
                if (!$type instanceof TypeUnion) {
                    return false;
                }

                $comparatorTypes = array_map(static fn(Type|TypeIntersect $t) => $t->name(), $type->types());
                $theseTypes = array_map(static fn(Type|TypeIntersect $t) => $t->name(), $this->types());

                sort($comparatorTypes);
                sort($theseTypes);

                return $comparatorTypes === $theseTypes;
            }
        };
    }

    private function createTypeIntersect(Type $one, Type $two, Type...$additional) : TypeIntersect {
        return new class(array_values([$one, $two, ...$additional])) implements TypeIntersect {
            public function __construct(
                /**
                 * @var non-empty-list<Type>
                 */
                private readonly array $types
            ) {
            }

            public function name() : string {
                return sprintf(
                    '%s',
                    join('&', array_map(static fn(Type $t) => $t->name(), $this->types()))
                );
            }

            /**
             * @return non-empty-list<Type>
             */
            public function types() : array {
                return $this->types;
            }

            public function equals(TypeUnion|TypeIntersect|Type $type) : bool {
                if (!$type instanceof TypeIntersect) {
                    return false;
                }

                $comparatorTypes = array_map(static fn(Type|TypeIntersect $t) => $t->name(), $type->types());
                $theseTypes = array_map(static fn(Type|TypeIntersect $t) => $t->name(), $this->types());

                sort($comparatorTypes);
                sort($theseTypes);

                return $comparatorTypes === $theseTypes;
            }
        };
    }
}
