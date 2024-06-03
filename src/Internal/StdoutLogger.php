<?php

namespace Cspray\AnnotatedContainer\Internal;

use DateTime;
use DateTimeImmutable;
use Psr\Log\AbstractLogger;
use Stringable;

/**
 * @deprecated
 */
final class StdoutLogger extends AbstractLogger {

    /**
     * @var callable():DateTimeImmutable
     */
    private $dateTimeProvider;

    /**
     * @param callable():DateTimeImmutable $dateTimeProvider
     */
    public function __construct(callable $dateTimeProvider) {
        $this->dateTimeProvider = $dateTimeProvider;
    }

    public function log($level, Stringable|string $message, array $context = []) : void {
        $format = '[%s] annotated-container.%s: %s %s%s';
        $contents = sprintf(
            $format,
            ($this->dateTimeProvider)()->format('Y-m-d\TH:i:s.uP'),
            strtoupper((string) $level),
            (string) $message,
            json_encode($context),
            PHP_EOL
        );
        fwrite(STDOUT, $contents);
    }
}
