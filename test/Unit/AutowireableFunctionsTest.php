<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Exception\InvalidAutowireParameter;
use Cspray\AnnotatedContainer\Exception\AutowireParameterNotFound;
use PHPUnit\Framework\TestCase;
use stdClass;
use function Cspray\Typiphy\objectType;
use function Cspray\AnnotatedContainer\rawParam;
use function Cspray\AnnotatedContainer\serviceParam;
use function Cspray\AnnotatedContainer\autowiredParams;

class AutowireableFunctionsTest extends TestCase {

    public static function nameProvider() : array {
        return [
            ['param'],
            ['paramName'],
            ['param_name'],
            ['paramname']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nameProvider')]
    public function testRawParameterGetName(string $name) {
        $param = rawParam($name, 'value');

        $this->assertSame($name, $param->getName());
    }

    public function testRawParameterWithEmptyNameThrowsException() {
        $this->expectException(InvalidAutowireParameter::class);
        $this->expectExceptionMessage('A parameter name must have a non-empty value.');
        rawParam('', []);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nameProvider')]
    public function testServiceParameterGetName(string $name) {
        $param = serviceParam($name, objectType(static::class));

        $this->assertSame($name, $param->getName());
    }

    public function testServiceParameterWithEmptyNameThrowsException() {
        $this->expectException(InvalidAutowireParameter::class);
        $this->expectExceptionMessage('A parameter name must have a non-empty value.');
        serviceParam('', objectType(static::class));
    }

    public static function valueProvider() : array {
        return [
            ['value'],
            [true],
            [null],
            [[1, 2, 3]],
            [42],
            [new stdClass()]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('valueProvider')]
    public function testRawParameterGetValue(mixed $value) {
        $param = rawParam('name', $value);

        $this->assertSame($value, $param->getValue());
    }

    public function testServiceParameterGetValue() {
        $param = serviceParam('foo', $type = objectType(static::class));

        $this->assertSame($type, $param->getValue());
    }

    public function testRawParameterIsServiceIdentifier() {
        $this->assertFalse(rawParam('name', null)->isServiceIdentifier());
    }

    public function testServiceParameterIsServiceIdentifier() {
        $this->assertTrue(serviceParam('foo', objectType(static::class))->isServiceIdentifier());
    }

    public function testAutowireableSetWithNoParamsIsEmpty() {
        $list = autowiredParams();

        $this->assertEmpty($list);
    }

    public function testAutowireableSetWithNoParamsIterator() {
        $arrayList = iterator_to_array(autowiredParams());

        $this->assertSame([], $arrayList);
    }

    public function testAutowireableSetWithNoParamsGetThrowsException() {
        $this->expectException(AutowireParameterNotFound::class);
        $this->expectExceptionMessage('There is no parameter found at index 1');
        autowiredParams()->get(1);
    }

    public function testAutowireableSetWithNoParamsHas() {
        $this->assertFalse(autowiredParams()->has(1));
    }

    public function testAutowireableSetAddIsNotEmpty() {
        $set = autowiredParams();
        $set->add(rawParam('foo', 'value'));

        $this->assertCount(1, $set);
    }

    public function testAutowireableSetAddIterator() {
        $set = autowiredParams();
        $set->add($param = rawParam('bar', 1234));

        $arraySet = iterator_to_array($set);
        $this->assertSame([$param], $arraySet);
    }

    public function testAutowireableSetAddGet() {
        $set = autowiredParams();
        $set->add($param = rawParam('bar', 1234));

        $this->assertSame($param, $set->get(0));
    }

    public function testAutowireableSetAddHas() {
        $set = autowiredParams();
        $set->add(rawParam('bar', 1234));

        $this->assertTrue($set->has(0));
    }

    public function testAutowireableSetOriginalParameters() {
        $set = autowiredParams(
            $one = rawParam('foo', 'value'),
            $two = serviceParam('bar', objectType(static::class))
        );

        $arraySet = iterator_to_array($set);

        $this->assertSame([$one, $two], $arraySet);
    }

    public function testAutowireableSetAddWithOriginalParameters() {
        $set = autowiredParams(
            $one = rawParam('foo', 'value'),
            $two = serviceParam('bar', objectType(static::class))
        );
        $set->add($three = rawParam('baz', 1234));

        $arraySet = iterator_to_array($set);
        $this->assertSame([$one, $two, $three], $arraySet);
    }

    public function testAutowireableSetWithDuplicateParameterNamesThrowsException() {
        $this->expectException(InvalidAutowireParameter::class);
        $this->expectExceptionMessage('A parameter named "foo" has already been added to this set.');
        autowiredParams(rawParam('foo', 'value'), serviceParam('foo', objectType(static::class)));
    }
}
