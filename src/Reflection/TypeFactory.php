<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Reflection;

use ReflectionNamedType;
use ReflectionType;

final class TypeFactory {

    public function fromReflection(?ReflectionType $reflectionType) : Type|TypeUnion|TypeIntersect {
        if ($reflectionType === null) {
            return $this->mixed();
        } elseif ($reflectionType instanceof ReflectionNamedType) {
            // if there is no name for the type it is implicitly mixed
            $name = $reflectionType->getName() ?? 'mixed';
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
        }

        return $type;
    }

    public function fromName(string $name) : Type {
        return match ($name) {
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
            default => $this->class($name),
        };
    }

    public function array() : Type {
        static $type;
        return $type ??= $this->createType('array');
    }

    public function bool() : Type {
        static $type;
        return $type ??= $this->createType('bool');
    }

    /**
     * @param class-string $class
     * @return self
     */
    public function class(string $class) : Type {
        static $types = [];
        return $types[$class] ??= $this->createType($class);
    }

    public function float() : Type {
        static $type;
        return $type ??= $this->createType('float');
    }

    public function int() : Type {
        static $type;
        return $type ??= $this->createType('int');
    }

    public function mixed() : Type {
        static $type;
        return $type ??= $this->createType('mixed');
    }

    public function never() : Type {
        static $type;
        return $type ??= $this->createType('never');
    }

    public function null() : Type {
        static $type;
        return $type ??= $this->createType('null');
    }

    public function object() : Type {
        static $type;
        return $type ??= $this->createType('object');
    }

    public function self() : Type {
        static $type;
        return $type ??= $this->createType('self');
    }

    public function static() : Type {
        static $type;
        return $type ??= $this->createType('static');
    }

    public function string() : Type {
        static $type;
        return $type ??= $this->createType('string');
    }

    public function void() : Type {
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

    private function createType(string $typeName) : Type {
        return new class($typeName) implements Type {
            public function __construct(
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
        return new class([$one, $two, ...$additional]) implements TypeUnion {
            private readonly string $name;
            public function __construct(
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
             * @return list<Type|TypeIntersect>
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
        return new class([$one, $two, ...$additional]) implements TypeIntersect {
            public function __construct(
                private readonly array $types
            ) {
            }

            public function name() : string {
                return sprintf(
                    '%s',
                    join('&', array_map(static fn(Type $t) => $t->name(), $this->types()))
                );
            }

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
