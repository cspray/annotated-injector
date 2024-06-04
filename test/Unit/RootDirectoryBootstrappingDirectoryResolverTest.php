<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Bootstrap\RootDirectoryBootstrappingDirectoryResolver;
use PHPUnit\Framework\TestCase;

final class RootDirectoryBootstrappingDirectoryResolverTest extends TestCase {

    public function testGetConfigurationPath() : void {
        $subject = new RootDirectoryBootstrappingDirectoryResolver('/root/dir');

        self::assertSame(
            '/root/dir/annotated-container.xml',
            $subject->configurationPath('annotated-container.xml')
        );
    }

    public function testGetCachePath() : void {
        $subject = new RootDirectoryBootstrappingDirectoryResolver('/root/dir');

        self::assertSame(
            '/root/dir/cache-dir',
            $subject->cachePath('cache-dir')
        );
    }

    public function testGetSourceScanPath() : void {
        $subject = new RootDirectoryBootstrappingDirectoryResolver('/root/path');

        self::assertSame(
            '/root/path/src',
            $subject->pathFromRoot('src')
        );
    }

    public function testVendorPath() : void {
        $subject = new RootDirectoryBootstrappingDirectoryResolver('/root/path');

        self::assertSame(
            '/root/path/vendor',
            $subject->vendorPath()
        );
    }
}
