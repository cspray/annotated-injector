<?php

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\ComposerJsonScanningThirdPartyInitializerProvider;
use Cspray\AnnotatedContainer\Bootstrap\PackagesComposerJsonPathProvider;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;
use Cspray\AnnotatedContainer\Exception\InvalidThirdPartyInitializer;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\Vendor\Package\FirstInitializer;
use Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\Vendor\Package\SecondInitializer;
use Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\Vendor\Package\ThirdInitializer;
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
                            'Cspray\\AnnotatedContainer\\Fixture\\VendorScanningInitializers\\Vendor\\Package\\FirstInitializer',
                            'Cspray\\AnnotatedContainer\\Fixture\\VendorScanningInitializers\\Vendor\\Package\\SecondInitializer',
                            'Cspray\\AnnotatedContainer\\Fixture\\VendorScanningInitializers\\Vendor\\Package\\ThirdInitializer'
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

    public function testHandleComposerJsonExtraNotArray() : void {
        $composerJsonProvider = $this->createMock(PackagesComposerJsonPathProvider::class);
        $composerJsonProvider->expects($this->once())
            ->method('composerJsonPaths')
            ->willReturn([
                '/path/to/vendor/package/composer.json'
            ]);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with('/path/to/vendor/package/composer.json')
            ->willReturn(json_encode([
                'name' => 'cspray/package',
                'extra' => 'not an array',
            ]));

        $provider = new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonProvider
        );

        self::assertSame([], $provider->thirdPartyInitializers());
    }

    public function testHandleComposerJsonExtraWithNoAnnotatedContainerKey() : void {
        $composerJsonProvider = $this->createMock(PackagesComposerJsonPathProvider::class);
        $composerJsonProvider->expects($this->once())
            ->method('composerJsonPaths')
            ->willReturn([
                '/path/to/vendor/package/composer.json'
            ]);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with('/path/to/vendor/package/composer.json')
            ->willReturn(json_encode([
                'name' => 'cspray/package',
                'extra' => [],
            ]));

        $provider = new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonProvider
        );

        self::assertSame([], $provider->thirdPartyInitializers());
    }

    public function testComposerJsonExtraWithAnnotatedContainerKeyNotArrayThrowsException() : void {
        $composerJsonProvider = $this->createMock(PackagesComposerJsonPathProvider::class);
        $composerJsonProvider->expects($this->once())
            ->method('composerJsonPaths')
            ->willReturn([
                '/path/to/vendor/package/composer.json'
            ]);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with('/path/to/vendor/package/composer.json')
            ->willReturn(json_encode([
                'name' => 'cspray/package',
                'extra' => [
                    '$annotatedContainer' => 'not an array'
                ],
            ]));


        $this->expectException(InvalidThirdPartyInitializer::class);
        $this->expectExceptionMessage('The value listed in composer.json extra.$annotatedContainer MUST be an array.');

        new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonProvider
        );
    }

    public function testComposerJsonExtraWithAnnotatedContainerKeyArrayHasNoInitializers() : void {
        $composerJsonProvider = $this->createMock(PackagesComposerJsonPathProvider::class);
        $composerJsonProvider->expects($this->once())
            ->method('composerJsonPaths')
            ->willReturn([
                '/path/to/vendor/package/composer.json'
            ]);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with('/path/to/vendor/package/composer.json')
            ->willReturn(json_encode([
                'name' => 'cspray/package',
                'extra' => [
                    '$annotatedContainer' => []
                ],
            ]));


        $this->expectException(InvalidThirdPartyInitializer::class);
        $this->expectExceptionMessage('The value listed in composer.json extra.$annotatedContainer MUST have a key "initializers" that holds a list of ThirdPartyInitializers to use.');

        new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonProvider
        );
    }

    public function testComposerJsonExtraWithAnnotatedContainerKeyArrayHasInitializersNotArray() : void {
        $composerJsonProvider = $this->createMock(PackagesComposerJsonPathProvider::class);
        $composerJsonProvider->expects($this->once())
            ->method('composerJsonPaths')
            ->willReturn([
                '/path/to/vendor/package/composer.json'
            ]);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('read')
            ->with('/path/to/vendor/package/composer.json')
            ->willReturn(json_encode([
                'name' => 'cspray/package',
                'extra' => [
                    '$annotatedContainer' => [
                        'initializers' => false
                    ]
                ],
            ]));


        $this->expectException(InvalidThirdPartyInitializer::class);
        $this->expectExceptionMessage('The value listed in composer.json extra.$annotatedContainer.initializers MUST be a list of ThirdPartyInitializers to use.');

        new ComposerJsonScanningThirdPartyInitializerProvider(
            $filesystem,
            $composerJsonProvider
        );
    }
}
