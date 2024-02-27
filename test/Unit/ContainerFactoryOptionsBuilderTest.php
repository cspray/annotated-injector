<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\Profiles;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ContainerFactoryOptionsBuilderTest extends TestCase {

    public function testGetProfiles() : void {
        $options = ContainerFactoryOptionsBuilder::forProfiles(Profiles::fromList(['default', 'dev', 'local']))
            ->build();

        self::assertSame(['default', 'dev', 'local'], $options->getProfiles()->toArray());
    }

    public function testWithLoggerImmutable() : void {
        $a = ContainerFactoryOptionsBuilder::forProfiles(Profiles::fromList(['default']));
        $b = $a->withLogger($this->getMockBuilder(LoggerInterface::class)->getMock());

        self::assertNotSame($a, $b);
    }

    public function testGetLogger() : void {
        $options = ContainerFactoryOptionsBuilder::forProfiles(Profiles::fromList(['default']))
            ->withLogger($logger = $this->getMockBuilder(LoggerInterface::class)->getMock())
            ->build();

        self::assertSame($logger, $options->getLogger());
    }
}
