<?php

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\ComposerJsonScanningThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Bootstrap\PackagesComposerJsonPathProvider;
use Cspray\AnnotatedContainer\Bootstrap\RootDirectoryBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\Exception\InvalidThirdPartyInitializer;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainerFixture\VendorScanningInitializers\FirstInitializer;
use Cspray\AnnotatedContainerFixture\VendorScanningInitializers\SecondInitializer;
use Cspray\AnnotatedContainerFixture\VendorScanningInitializers\ThirdInitializer;
use PHPUnit\Framework\TestCase;

class ComposerJsonScanningThirdPartyInitializerProviderTest extends TestCase {

    public function testComposerJsonListsThirdPartyInitializerProviderClassesListsCorrectObjects() : void {
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

    public function testComposerJsonInitializersAreNotClassThrowsException() : void {
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
                            'not a class string',
                        ]
                    ]
                ]
            ]));

        $this->expectException(InvalidThirdPartyInitializer::class);
        $thirdPartyInitializerProvider = ThirdPartyInitializer::class;
        $this->expectExceptionMessage(
            "Values listed in $rootDir/vendor/cspray/package/composer.json extra.\$annotatedContainer.initializers MUST " .
            "be a class-string that is an instance of $thirdPartyInitializerProvider but the value \"'not a class string'\" " .
            "is present."
        );

        new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonProvider
        );
    }

    public function testComposerJsonInitializersClassButNotThirdPartyInitializerProvider() : void {
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
                            'stdClass',
                        ]
                    ]
                ]
            ]));

        $this->expectException(InvalidThirdPartyInitializer::class);
        $thirdPartyInitializer = ThirdPartyInitializer::class;
        $this->expectExceptionMessage(
            "Values listed in $rootDir/vendor/cspray/package/composer.json extra.\$annotatedContainer.initializers MUST " .
            "be a class-string that is an instance of $thirdPartyInitializer but a value that is an instance of " .
            "\"stdClass\" is present."
        );

        new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonProvider
        );
    }

    public function testComposerJsonHasNonStringValueThrowsException() : void {
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
                            42,
                        ]
                    ]
                ]
            ]));

        $this->expectException(InvalidThirdPartyInitializer::class);
        $thirdPartyInitializerProvider = ThirdPartyInitializer::class;
        $this->expectExceptionMessage(
            "Values listed in $rootDir/vendor/cspray/package/composer.json extra.\$annotatedContainer.initializers MUST " .
            "be a class-string that is an instance of $thirdPartyInitializerProvider but the value \"42\" " .
            "is present."
        );

        new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonProvider
        );
    }
}
