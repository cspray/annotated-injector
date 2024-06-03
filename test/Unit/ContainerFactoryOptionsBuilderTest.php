<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ContainerFactoryOptionsBuilderTest extends TestCase {

    public function testGetActiveProfiles() : void {
        $options = ContainerFactoryOptionsBuilder::forActiveProfiles('default', 'dev', 'local')
            ->build();

        self::assertSame(['default', 'dev', 'local'], $options->getActiveProfiles());
    }

    public function testWithLoggerImmutable() : void {
        $a = ContainerFactoryOptionsBuilder::forActiveProfiles('default');
        $b = $a->withLogger($this->getMockBuilder(LoggerInterface::class)->getMock());

        self::assertNotSame($a, $b);
    }

    public function testGetLogger() : void {
        $options = ContainerFactoryOptionsBuilder::forActiveProfiles('default')
            ->withLogger($logger = $this->getMockBuilder(LoggerInterface::class)->getMock())
            ->build();

        self::assertSame($logger, $options->getLogger());
    }
}
