<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Composer\InstalledVersions;
use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use PHPUnit\Framework\TestCase;

class AnnotatedContainerVersionTest extends TestCase {

    public function testGetApiVersionReturnsVersionFileContents() : void {
        self::assertSame(
            InstalledVersions::getVersion('cspray/annotated-container'),
            AnnotatedContainerVersion::version()
        );
    }
}
