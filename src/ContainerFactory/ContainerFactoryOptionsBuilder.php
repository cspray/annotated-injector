<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Profiles;
use Psr\Log\LoggerInterface;

final class ContainerFactoryOptionsBuilder {

    private Profiles $activeProfiles;
    private ?LoggerInterface $logger = null;

    private function __construct() {
    }

    public static function forProfiles(Profiles $profiles) : self {
        $instance = new self;
        $instance->activeProfiles = $profiles;
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
                private readonly Profiles $activeProfiles,
                private readonly ?LoggerInterface $logger
            ) {
            }

            public function getProfiles(): Profiles {
                return $this->activeProfiles;
            }

            public function getLogger() : ?LoggerInterface {
                return $this->logger;
            }
        };
    }
}
