<?php

namespace Cspray\AnnotatedContainer\Integration;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use PHPUnit\Framework\TestCase;

class ConsoleCommandTest extends TestCase {

    public function testRunningConsoleCommandListsAppNameAndVersion() : void {
        exec(__DIR__ . '/../../bin/annotated-container', $output, $result);

        self::assertSame(0, $result);
        self::assertStringContainsString('Annotated Container ' . AnnotatedContainerVersion::version(), implode(PHP_EOL, $output));
    }
}
