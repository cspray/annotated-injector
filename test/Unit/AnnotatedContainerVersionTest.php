<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use PackageVersions\Versions;
use PHPUnit\Framework\TestCase;

class AnnotatedContainerVersionTest extends TestCase {

    public function testGetApiVersionReturnsVersionFileContents() : void {
        self::assertSame(
            Versions::getVersion('cspray/annotated-container'),
            AnnotatedContainerVersion::version()
        );
    }
}
