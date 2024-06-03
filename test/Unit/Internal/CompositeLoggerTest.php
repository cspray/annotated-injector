<?php

namespace Cspray\AnnotatedContainer\Unit\Internal;

use Cspray\AnnotatedContainer\Internal\CompositeLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class CompositeLoggerTest extends TestCase {

    public static function loggerMethodProvider() : array {
        return [
            ['emergency'],
            ['alert'],
            ['critical'],
            ['error'],
            ['warning'],
            ['notice'],
            ['info'],
            ['debug']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('loggerMethodProvider')]
    public function testLoggersPassedToCompositeAreCalled(string $method) : void {
        $one = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $two = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $one->expects($this->once())
            ->method($method)
            ->with('My message', ['my' => 'context']);
        $two->expects($this->once())
            ->method($method)
            ->with('My message', ['my' => 'context']);

        $logger = new CompositeLogger($one, $two);

        $logger->$method('My message', ['my' => 'context']);
    }

    public function testLoggersPassedToLogAreCalled() : void {
        $one = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $two = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $one->expects($this->once())
            ->method('log')
            ->with('LEVEL', 'My message', ['my' => 'context']);
        $two->expects($this->once())
            ->method('log')
            ->with('LEVEL', 'My message', ['my' => 'context']);

        $logger = new CompositeLogger($one, $two);

        $logger->log('LEVEL', 'My message', ['my' => 'context']);
    }
}
