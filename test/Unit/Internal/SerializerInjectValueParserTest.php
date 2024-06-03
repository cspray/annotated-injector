<?php

namespace Cspray\AnnotatedContainer\Unit\Internal;

use Cspray\AnnotatedContainer\Internal\SerializerInjectValueParser;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use PHPUnit\Framework\TestCase;
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
use function Cspray\Typiphy\voidType;

final class SerializerInjectValueParserTest extends TestCase {

    public function parseStringTypeProvider() : array {
        return [
            ['string', stringType()],
            ['int', intType()],
            ['float', floatType()],
            ['bool', boolType()],
            ['array', arrayType()],
            ['mixed', mixedType()],
            ['iterable', iterableType()],
            ['null', nullType()],
            ['void', voidType()],
            ['callable', callableType()],
            [$this::class, objectType($this::class)]
        ];
    }

    /**
     * @dataProvider parseStringTypeProvider
     */
    public function testParseStringToType(string $stringType, Type|ObjectType $type) : void {
        $actual = (new SerializerInjectValueParser())->convertStringToType($stringType);

        self::assertSame($type, $actual);
    }
}
