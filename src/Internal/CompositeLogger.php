<?php

namespace Cspray\AnnotatedContainer\Internal;

use Psr\Log\LoggerInterface;
use Stringable;

/**
 * @deprecated
 */
final class CompositeLogger implements LoggerInterface {

    /** @var list<LoggerInterface> */
    private readonly array $loggers;

    public function __construct(
        LoggerInterface $logger,
        LoggerInterface...$additionalLoggers
    ) {
        $this->loggers = [$logger, ...$additionalLoggers];
    }

    /**
     * @return list<LoggerInterface>
     */
    public function getLoggers() : array {
        return $this->loggers;
    }

    public function emergency(Stringable|string $message, array $context = []) : void {
        foreach ($this->loggers as $logger) {
            $logger->emergency($message, $context);
        }
    }

    public function alert(Stringable|string $message, array $context = []) : void {
        foreach ($this->loggers as $logger) {
            $logger->alert($message, $context);
        }
    }

    public function critical(Stringable|string $message, array $context = []) : void {
        foreach ($this->loggers as $logger) {
            $logger->critical($message, $context);
        }
    }

    public function error(Stringable|string $message, array $context = []) : void {
        foreach ($this->loggers as $logger) {
            $logger->error($message, $context);
        }
    }

    public function warning(Stringable|string $message, array $context = []) : void {
        foreach ($this->loggers as $logger) {
            $logger->warning($message, $context);
        }
    }

    public function notice(Stringable|string $message, array $context = []) : void {
        foreach ($this->loggers as $logger) {
            $logger->notice($message, $context);
        }
    }

    public function info(Stringable|string $message, array $context = []) : void {
        foreach ($this->loggers as $logger) {
            $logger->info($message, $context);
        }
    }

    public function debug(Stringable|string $message, array $context = []) : void {
        foreach ($this->loggers as $logger) {
            $logger->debug($message, $context);
        }
    }

    public function log($level, Stringable|string $message, array $context = []) : void {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}
