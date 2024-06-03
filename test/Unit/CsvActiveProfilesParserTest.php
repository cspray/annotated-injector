<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Profiles\CsvActiveProfilesParser;
use PHPUnit\Framework\TestCase;

class CsvActiveProfilesParserTest extends TestCase {

    private function getSubject() : CsvActiveProfilesParser {
        return new CsvActiveProfilesParser();
    }

    public function testEmptyStringThrowsException() : void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The profiles to parse cannot be an empty string.');
        $this->getSubject()->parse('');
    }

    public function testStringWithoutCommaReturnsCorrectArray() : void {
        $this->assertSame(['foo'], $this->getSubject()->parse('foo'));
    }

    public function testStringWithCommaReturnsCorrectArray() : void {
        $this->assertSame(['foo', 'bar', 'baz'], $this->getSubject()->parse('foo,bar,baz'));
    }

    public function testTrimsSpaceFromListItems() : void {
        $this->assertSame(['foo', 'qux', 'quz'], $this->getSubject()->parse('foo, qux ,   quz    '));
    }

    public function testEmptyProfilesIgnored() : void {
        $this->assertSame(['foo', 'bar'], $this->getSubject()->parse('foo,,bar'));
    }

    public function testNonEmptyStringResultsEmptyArrayThrowsException() : void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("The profile string ',,' results in no valid profiles.");
        $this->getSubject()->parse(',,');
    }
}
