<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis;

use Cspray\AnnotatedContainer\Exception\InvalidAnnotatedTarget;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use PHPUnit\Framework\TestCase;

final class AnnotatedTargetDefinitionConverterTest extends TestCase {

    public function testAnnotatedTargetNotKnownAttributeTypeThrowsException() : void {
        $subject = new AnnotatedTargetDefinitionConverter();

        $target = $this->createMock(AnnotatedTarget::class);
        $target->expects($this->once())->method('attributeInstance')->willReturn(new \stdClass());

        $this->expectException(InvalidAnnotatedTarget::class);
        $this->expectExceptionMessage(
            'Received an AnnotatedTarget with an attribute instance of an unknown type. This is indicative of an ' .
            'internal error and should be reported to https://github.com/cspray/annotated-container.'
        );

        $subject->convert($target);
    }
}
