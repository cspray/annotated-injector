<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\ComposerRuntimePackagesComposerJsonPathProvider;
use PHPUnit\Framework\TestCase;

class ComposerRuntimePackagesComposerJsonPathProviderTest extends TestCase {

    public function testCorrectPathsAreReturnedBasedOnInstalledPackages() : void {
        $vendorDir = dirname(__DIR__, 3) . '/vendor';
        $phpunitToolsDir = dirname(__DIR__, 3) . '/tools/phpunit';
        $expected = [
            $vendorDir . '/cspray/annotated-container-adr/composer.json',
            $vendorDir . '/cspray/annotated-container-attribute/composer.json',
            $vendorDir . '/cspray/annotated-target/composer.json',
            $vendorDir . '/cspray/architectural-decision/composer.json',
            $vendorDir . '/cspray/precision-stopwatch/composer.json',
            $vendorDir . '/illuminate/container/composer.json',
            $vendorDir . '/illuminate/contracts/composer.json',
            $vendorDir . '/jetbrains/phpstorm-attributes/composer.json',
            $vendorDir . '/laravel/serializable-closure/composer.json',
            $vendorDir . '/nikic/php-parser/composer.json',
            $vendorDir . '/php-di/invoker/composer.json',
            $vendorDir . '/php-di/php-di/composer.json',
            $vendorDir . '/psr/container/composer.json',
            $vendorDir . '/psr/log/composer.json',
            $vendorDir . '/psr/simple-cache/composer.json',
            $vendorDir . '/rdlowrey/auryn/composer.json',
            $phpunitToolsDir . '/composer.json',
            $phpunitToolsDir . '/vendor/cspray/assert-throws/composer.json',
            $phpunitToolsDir . '/vendor/cspray/stream-buffer-intercept/composer.json',
            $phpunitToolsDir . '/vendor/mikey179/vfsstream/composer.json',
            $phpunitToolsDir . '/vendor/myclabs/deep-copy/composer.json',
            $phpunitToolsDir . '/vendor/phar-io/manifest/composer.json',
            $phpunitToolsDir . '/vendor/phar-io/version/composer.json',
            $phpunitToolsDir . '/vendor/phpunit/php-code-coverage/composer.json',
            $phpunitToolsDir . '/vendor/phpunit/php-file-iterator/composer.json',
            $phpunitToolsDir . '/vendor/phpunit/php-invoker/composer.json',
            $phpunitToolsDir . '/vendor/phpunit/php-text-template/composer.json',
            $phpunitToolsDir . '/vendor/phpunit/php-timer/composer.json',
            $phpunitToolsDir . '/vendor/phpunit/phpunit/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/cli-parser/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/code-unit/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/code-unit-reverse-lookup/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/comparator/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/complexity/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/diff/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/environment/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/exporter/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/global-state/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/lines-of-code/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/object-enumerator/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/object-reflector/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/recursion-context/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/type/composer.json',
            $phpunitToolsDir . '/vendor/sebastian/version/composer.json',
            $phpunitToolsDir . '/vendor/theseer/tokenizer/composer.json',
        ];

        $subject = new ComposerRuntimePackagesComposerJsonPathProvider();

        self::assertSame($expected, $subject->composerJsonPaths());
    }
}
