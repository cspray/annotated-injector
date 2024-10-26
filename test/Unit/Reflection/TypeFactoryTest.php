<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Reflection;

use Closure;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeEqualityComparator;
use Cspray\AnnotatedContainer\Reflection\TypeFactory;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeFactoryTest extends TestCase {

    public static function typeProvider() : array {
        return [
            'array' => [static fn(TypeFactory $typeFactory) => $typeFactory->array(), 'array'],
            'bool' => [static fn(TypeFactory $typeFactory) => $typeFactory->bool(), 'bool'],
            'class' => [static fn(TypeFactory $typeFactory) => $typeFactory->class(Type::class), Type::class],
            'float' => [static fn(TypeFactory $typeFactory) => $typeFactory->float(), 'float'],
            'int' => [static fn(TypeFactory $typeFactory) => $typeFactory->int(), 'int'],
            'mixed' => [static fn(TypeFactory $typeFactory) => $typeFactory->mixed(), 'mixed'],
            'never' => [static fn(TypeFactory $typeFactory) => $typeFactory->never(), 'never'],
            'null' => [static fn(TypeFactory $typeFactory) => $typeFactory->null(), 'null'],
            'object' => [static fn(TypeFactory $typeFactory) => $typeFactory->object(), 'object'],
            'string' => [static fn(TypeFactory $typeFactory) => $typeFactory->string(), 'string'],
            'void' => [static fn(TypeFactory $typeFactory) => $typeFactory->void(), 'void'],
            'self' => [static fn(TypeFactory $typeFactory) => $typeFactory->self(), 'self'],
            'static' => [static fn(TypeFactory $typeFactory) => $typeFactory->static(), 'static'],
        ];
    }

    #[DataProvider('typeProvider')]
    /**
     * @param Closure(TypeFactory):Type $typeCreator
     * @param non-empty-string $expectedName
     * @return void
     */
    public function testTypeReturnsCorrectName(
        Closure $typeCreator,
        string $expectedName
    ) : void {
        $typeFactory = new TypeFactory();
        self::assertSame($expectedName, $typeCreator($typeFactory)->name());
    }

    #[DataProvider('typeProvider')]
    /**
     * @param Closure(TypeFactory):Type $typeCreator
     * @return void
     */
    public function testTypeFactoryReturnsSameObjectEachTimeForNonUnionOrIntersectTypes(Closure $typeCreator) : void {
        $typeFactory = new TypeFactory();
        self::assertSame($typeCreator($typeFactory), $typeCreator($typeFactory));
    }

    public function testTypeFactoryUnionReturnsAppropriateTypes() : void {
        $typeFactory = new TypeFactory();

        $one = $typeFactory->string();
        $two = $typeFactory->float();

        $union = $typeFactory->union($one, $two);

        self::assertSame([$one, $two], $union->types());
    }

    public function testTypeFactoryIntersectReturnsAppropriateTypes() : void {
        $typeFactory = new TypeFactory();

        $one = $typeFactory->class(Type::class);
        $two = $typeFactory->class(TypeIntersect::class);

        $intersect = $typeFactory->intersect($one, $two);

        self::assertSame([$one, $two], $intersect->types());
    }

    public function testTypeFactoryUnionSupportsMoreThanTwoTypesIncludingTypeIntersect() : void {
        $typeFactory = new TypeFactory();

        $one = $typeFactory->int();
        $two = $typeFactory->string();
        $three = $typeFactory->intersect(
            $typeFactory->class(Type::class),
            $typeFactory->class(TestCase::class)
        );
        $four = $typeFactory->float();

        $union = $typeFactory->union(
            $one,
            $two,
            $three,
            $four
        );

        self::assertSame([$one, $two, $three, $four], $union->types());
        self::assertSame(
            sprintf('int|string|(%s&%s)|float', Type::class, TestCase::class),
            $union->name()
        );
    }

    public function testTypeFactoryIntersectSupportsMoreThanTwoTypes() : void {
        $typeFactory = new TypeFactory();

        $one = $typeFactory->class(Type::class);
        $two = $typeFactory->class(TypeUnion::class);
        $three = $typeFactory->class(TypeIntersect::class);
        $four = $typeFactory->class(TypeFactory::class);

        $intersect = $typeFactory->intersect($one, $two, $three, $four);

        self::assertSame([$one, $two, $three, $four], $intersect->types());
    }

    public function testTypeFactoryNullableCreatesCorrectUnion() : void {
        $typeFactory = new TypeFactory();

        $type = $typeFactory->nullable($typeFactory->int());

        self::assertSame([$typeFactory->null(), $typeFactory->int()], $type->types());
    }

    public static function typeFromReflectionProvider(): array {
        return [
            'array' => ['array', static fn(TypeFactory $typeFactory) => $typeFactory->array()],
            'bool' => ['bool', static fn(TypeFactory $typeFactory) => $typeFactory->bool()],
            'float' => ['float', static fn(TypeFactory $typeFactory) => $typeFactory->float()],
            'int' => ['int', static fn(TypeFactory $typeFactory) => $typeFactory->int()],
            'explicit-mixed' => ['mixed', static fn(TypeFactory $typeFactory) => $typeFactory->mixed()],
            'implicit-mixed' => ['implicitMixed', static fn(TypeFactory $typeFactory) => $typeFactory->mixed()],
            'never' => ['never', static fn(TypeFactory $typeFactory) => $typeFactory->never()],
            'null' => ['null', static fn(TypeFactory $typeFactory) => $typeFactory->null()],
            'object' => ['object', static fn(TypeFactory $typeFactory) => $typeFactory->object()],
            'class' => ['class', static fn(TypeFactory $typeFactory) => $typeFactory->class(TypeFactory::class)],
            'self' => ['self', static fn(TypeFactory $typeFactory) => $typeFactory->self()],
            'static' => ['static', static fn(TypeFactory $typeFactory) => $typeFactory->static()],
        ];
    }

    #[DataProvider('typeFromReflectionProvider')]
    /**
     * @param Closure(TypeFactory):Type $expectedTypeCreator
     */
    public function testTypeFromReflectionType(string $method, Closure $expectedTypeCreator): void {
        $reflection = new \ReflectionObject($this->reflectionTypeClass());
        $method = $reflection->getMethod($method);
        $reflectionType = $method->getReturnType();

        $typeFactory = new TypeFactory();
        $actual = $typeFactory->fromReflection($reflectionType);

        self::assertSame(
            $actual,
            $expectedTypeCreator($typeFactory),
        );
    }

    public static function typeUnionFromReflectionProvider() : array {
        return [
            'nullable' => ['nullableInt', static fn(TypeFactory $typeFactory) => $typeFactory->union(
                $typeFactory->null(),
                $typeFactory->int()
            ), 'null|int'],
            'numbers' => ['numbers', static fn(TypeFactory $typeFactory) => $typeFactory->union(
                $typeFactory->int(),
                $typeFactory->float()
            ), 'int|float'],
            'manyTypesInUnion' => ['manyTypesInUnion', static fn(TypeFactory $typeFactory) => $typeFactory->union(
                $typeFactory->int(),
                $typeFactory->float(),
                $typeFactory->bool(),
                $typeFactory->string(),
            ), 'string|int|float|bool']
        ];
    }

    #[DataProvider('typeUnionFromReflectionProvider')]
    /**
     * @param Closure(TypeFactory):TypeUnion $typeCreator
     */
    public function testTypeUnionFromReflectionTypeHasCorrectTypes(string $method, Closure $typeCreator, string $expectedName): void {
        $reflection = new \ReflectionObject($this->reflectionTypeClass());
        $method = $reflection->getMethod($method);
        $reflectionType = $method->getReturnType();

        $typeFactory = new TypeFactory();
        $actual = $typeFactory->fromReflection($reflectionType);

        $expectedTypes = $typeCreator($typeFactory)->types();
        $actualTypes = $actual->types();
        $sortByName = static fn(Type $a, Type $b) => $a->name() <=> $b->name();
        usort($expectedTypes, $sortByName);
        usort($actualTypes, $sortByName);

        self::assertInstanceOf(TypeUnion::class, $actual);
        self::assertSame($expectedTypes, $actualTypes);
        self::assertSame($expectedName, $actual->name());
    }

    public static function typeIntersectFromReflectionProvider() : array {
        return [
            'simple-type-intersect' => ['intersect', static fn(TypeFactory $typeFactory) => $typeFactory->intersect(
                $typeFactory->class(Type::class),
                $typeFactory->class(TypeUnion::class)
            ), sprintf('%s&%s', Type::class, TypeUnion::class)]
        ];
    }

    #[DataProvider('typeIntersectFromReflectionProvider')]
    /**
     * @param Closure(TypeFactory):TypeIntersect $typeCreator
     */
    public function testTypeIntersectFromReflectionType(string $method, Closure $typeCreator, string $expectedName): void {
        $reflection = new \ReflectionObject($this->reflectionTypeClass());
        $method = $reflection->getMethod($method);
        $reflectionType = $method->getReturnType();

        $typeFactory = new TypeFactory();
        $actual = $typeFactory->fromReflection($reflectionType);

        $expectedTypes = $typeCreator($typeFactory)->types();
        $actualTypes = $actual->types();
        $sortByName = static fn(Type $a, Type $b) => $a->name() <=> $b->name();
        usort($expectedTypes, $sortByName);
        usort($actualTypes, $sortByName);

        self::assertInstanceOf(TypeIntersect::class, $actual);
        self::assertSame($expectedTypes, $actualTypes);
        self::assertSame($expectedName, $actual->name());
    }

    public static function typeFromNameProvider() : array {
        return [
            'array' => ['array', static fn(TypeFactory $typeFactory) => $typeFactory->array()],
            'bool' => ['bool', static fn(TypeFactory $typeFactory) => $typeFactory->bool()],
            'float' => ['float', static fn(TypeFactory $typeFactory) => $typeFactory->float()],
            'int' => ['int', static fn(TypeFactory $typeFactory) => $typeFactory->int()],
            'mixed' => ['mixed', static fn(TypeFactory $typeFactory) => $typeFactory->mixed()],
            'never' => ['never', static fn(TypeFactory $typeFactory) => $typeFactory->never()],
            'null' => ['null', static fn(TypeFactory $typeFactory) => $typeFactory->null()],
            'object' => ['object', static fn(TypeFactory $typeFactory) => $typeFactory->object()],
            'self' => ['self', static fn(TypeFactory $typeFactory) => $typeFactory->self()],
            'static' => ['static', static fn(TypeFactory $typeFactory) => $typeFactory->static()],
            'string' => ['string', static fn(TypeFactory $typeFactory) => $typeFactory->string()],
            'void' => ['void', static fn(TypeFactory $typeFactory) => $typeFactory->void()],
            'class' => [TypeFactory::class, static fn(TypeFactory $typeFactory) => $typeFactory->class(TypeFactory::class)],
        ];
    }

    #[DataProvider('typeFromNameProvider')]
    /**
     * @param string $typeName
     * @param Closure $expectedTypeCreator
     * @return void
     */
    public function testTypeFromNameCreatesCorrectObject(string $typeName, Closure $expectedTypeCreator) : void {
        $typeFactory = new TypeFactory();
        $actual = $typeFactory->fromName($typeName);

        self::assertSame($actual, $expectedTypeCreator($typeFactory));
    }

    public static function typeEqualityProvider() : array {
        return [
            'stringAndString' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->string(),
                static fn(TypeFactory $typeFactory) => $typeFactory->string(),
                true,
            ],
            'stringAndInt' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->string(),
                static fn(TypeFactory $typeFactory) => $typeFactory->int(),
                false,
            ],
            'intAndClass' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->int(),
                static fn(TypeFactory $typeFactory) => $typeFactory->class(TypeFactory::class),
                false,
            ],
            'sameClass' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->class(TypeFactory::class),
                static fn(TypeFactory $typeFactory) => $typeFactory->class(TypeFactory::class),
                true,
            ],
            'differentClasses' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->class(TypeFactory::class),
                static fn(TypeFactory $typeFactory) => $typeFactory->class(Type::class),
                false,
            ],
            'typeAndTypeUnion' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->string(),
                static fn(TypeFactory $typeFactory) => $typeFactory->nullable($typeFactory->string()),
                false,
            ],
            'typeAndTypeIntersect' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->class(Type::class),
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect(
                    $typeFactory->class(Type::class),
                    $typeFactory->class(TypeEqualityComparator::class),
                ),
                false
            ],
            'nullableSameTypes' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->nullable($typeFactory->string()),
                static fn(TypeFactory $typeFactory) => $typeFactory->nullable($typeFactory->string()),
                true,
            ],
            'typeUnionAndType' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->union($typeFactory->int(), $typeFactory->float()),
                static fn(TypeFactory $typeFactory) => $typeFactory->float(),
                false,
            ],
            'typeUnionAndTypeIntersect' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->union($typeFactory->class(Type::class), $typeFactory->class(TypeUnion::class)),
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect($typeFactory->class(Type::class), $typeFactory->class(TypeUnion::class)),
                false,
            ],
            'typeUnionDifferentTypes' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->union($typeFactory->int(), $typeFactory->float()),
                static fn(TypeFactory $typeFactory) => $typeFactory->union($typeFactory->string(), $typeFactory->int()),
                false,
            ],
            'typeUnionDifferentOrders' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->union($typeFactory->int(), $typeFactory->float()),
                static fn(TypeFactory $typeFactory) => $typeFactory->union($typeFactory->float(), $typeFactory->int()),
                true
            ],
            'typeIntersectSameClasses' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect($typeFactory->class(Type::class), $typeFactory->class(TypeIntersect::class)),
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect($typeFactory->class(Type::class), $typeFactory->class(TypeIntersect::class)),
                true
            ],
            'typeIntersectAndType' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect($typeFactory->class(Type::class), $typeFactory->class(TypeEqualityComparator::class)),
                static fn(TypeFactory $typeFactory) => $typeFactory->array(),
                false,
            ],
            'typeIntersectAndTypeUnion' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect($typeFactory->class(Type::class), $typeFactory->class(TypeEqualityComparator::class)),
                static fn(TypeFactory $typeFactory) => $typeFactory->union($typeFactory->string(), $typeFactory->float()),
                false,
            ],
            'typeIntersectDifferentClasses' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect($typeFactory->class(Type::class), $typeFactory->class(TypeEqualityComparator::class)),
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect($typeFactory->class(Type::class), $typeFactory->class(TypeFactory::class)),
                false,
            ],
            'typeIntersectDifferentOrders' => [
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect($typeFactory->class(Type::class), $typeFactory->class(TypeIntersect::class)),
                static fn(TypeFactory $typeFactory) => $typeFactory->intersect($typeFactory->class(TypeIntersect::class), $typeFactory->class(Type::class)),
                true
            ]
        ];
    }

    #[DataProvider('typeEqualityProvider')]
    /**
     * @param Closure():Type|TypeUnion|TypeIntersect $aType
     * @param Closure():Type|TypeUnion|TypeIntersect $bType
     * @param bool $expected
     * @return void
     */
    public function testTypeEqualityComparison(
        Closure $aType,
        Closure $bType,
        bool $expected
    ) : void {
        $typeFactory = new TypeFactory();
        $a = $aType($typeFactory);
        $b = $bType($typeFactory);

        self::assertSame($expected, $a->equals($b));
    }

    private function reflectionTypeClass(): object {
        return new class {
            public function array() : array {
                return [];
            }

            public function bool() : bool {
                return true;
            }

            public function float() : float {
                return 3.14;
            }

            public function int() : int {
                return 0;
            }

            public function mixed() : mixed {
                return 'mixed';
            }

            public function implicitMixed() {
                return $this->mixed();
            }

            public function never() : never {
                throw new \RuntimeException();
            }

            public function null() : null {
                return null;
            }

            public function object() : object {
                return new \stdClass();
            }

            public function self() : self {
                return $this;
            }

            public function static() : static {
                return $this;
            }

            public function string() : string {
                return 'string';
            }

            public function void() : void {
            }

            public function class() : TypeFactory {
                return new TypeFactory();
            }

            public function nullableInt() : ?int {
                return null;
            }

            public function numbers() : int|float {
                return 1;
            }

            public function intersect() : Type&TypeUnion {
            }

            public function manyTypesInUnion() : int|float|bool|string {
                return false;
            }
        };
    }
}
