<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\VendorPresenceBasedBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VendorPresenceBasedBootstrappingDirectoryResolverTest extends TestCase {

    private MockObject&Filesystem $filesystem;

    protected function setUp() : void {
        $this->filesystem = $this->createMock(Filesystem::class);
    }

    public function testRanFromAnnotatedContainerRootReturnsCorrectPaths() : void {
        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with('/root/vendor/autoload.php')
            ->willReturn(true);

        $subject = new VendorPresenceBasedBootstrappingDirectoryResolver(
            $this->filesystem,
            '/root/src/Bootstrap'
        );

        self::assertSame('/root/root-sub-path', $subject->rootPath('root-sub-path'));
        self::assertSame('/root/config-sub-path', $subject->configurationPath('config-sub-path'));
    }

    public function testRanFromAnnotatedContainerInstalledThroughComposerReturnsCorrectPaths() : void {
        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with('/root/vendor/cspray/annotated-container/vendor/autoload.php')
            ->willReturn(false);

        $subject = new VendorPresenceBasedBootstrappingDirectoryResolver(
            $this->filesystem,
            '/root/vendor/cspray/annotated-container/src/Bootstrap'
        );

        self::assertSame('/root/root-sub-path', $subject->rootPath('root-sub-path'));
        self::assertSame('/root/config-sub-path', $subject->configurationPath('config-sub-path'));
    }

}