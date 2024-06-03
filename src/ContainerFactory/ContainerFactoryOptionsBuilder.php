<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Psr\Log\LoggerInterface;

final class ContainerFactoryOptionsBuilder {

    private array $activeProfiles;
    private ?LoggerInterface $logger = null;

    private function __construct() {
    }

    public static function forActiveProfiles(string $profile, string...$additionalProfiles) : self {
        $instance = new self;
        $instance->activeProfiles = [$profile, ...$additionalProfiles];
        return $instance;
    }

    /**
     * @deprecated
     */
    public function withLogger(LoggerInterface $logger) : self {
        $instance = clone $this;
        $instance->logger = $logger;
        return $instance;
    }

    public function build() : ContainerFactoryOptions {
        return new class($this->activeProfiles, $this->logger) implements ContainerFactoryOptions {
            public function __construct(
                private readonly array $activeProfiles,
                private readonly ?LoggerInterface $logger
            ) {
            }

            public function getActiveProfiles(): array {
                return $this->activeProfiles;
            }

            public function getLogger() : ?LoggerInterface {
                return $this->logger;
            }
        };
    }
}
