<?php

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\ComposerJsonScanningThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Bootstrap\PackagesComposerJsonPathProvider;
use Cspray\AnnotatedContainer\Bootstrap\RootDirectoryBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainerFixture\VendorScanningInitializers\FirstInitializer;
use Cspray\AnnotatedContainerFixture\VendorScanningInitializers\SecondInitializer;
use Cspray\AnnotatedContainerFixture\VendorScanningInitializers\ThirdInitializer;
use PHPUnit\Framework\TestCase;

class VendorScanningThirdPartyInitializerProviderTest extends TestCase {

    public function testVendorScanningProviderIncludesCorrectClasses() : void {
        $rootDir = __DIR__ . '/../../../fixture_src/VendorScanningInitializers';
        $composerJsonProvider = $this->createMock(PackagesComposerJsonPathProvider::class);
        $composerJsonProvider->expects($this->once())
            ->method('composerJsonPaths')
            ->willReturn([
                  $rootDir . '/vendor/cspray/package/composer.json'
            ]);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with($rootDir . '/vendor/cspray/package/composer.json')
            ->willReturn(json_encode([
                'name' => 'cspray/package',
                'extra' => [
                    '$annotatedContainer' => [
                        'initializers' => [
                            'Cspray\\AnnotatedContainerFixture\\VendorScanningInitializers\\FirstInitializer',
                            'Cspray\\AnnotatedContainerFixture\\VendorScanningInitializers\\SecondInitializer',
                            'Cspray\\AnnotatedContainerFixture\\VendorScanningInitializers\\ThirdInitializer'
                        ]
                    ]
                ]
            ]));

        $subject = new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonProvider
        );
        $initializers = $subject->thirdPartyInitializers();

        self::assertCount(3, $initializers);
        self::assertInstanceOf(FirstInitializer::class, $initializers[0]);
        self::assertInstanceOf(SecondInitializer::class, $initializers[1]);
        self::assertInstanceOf(ThirdInitializer::class, $initializers[2]);
    }
}
