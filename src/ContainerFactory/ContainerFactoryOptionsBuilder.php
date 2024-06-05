<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Profiles;

final class ContainerFactoryOptionsBuilder {

    private Profiles $activeProfiles;

    private function __construct() {
    }

    public static function forProfiles(Profiles $profiles) : self {
        $instance = new self;
        $instance->activeProfiles = $profiles;
        return $instance;
    }

    public function build() : ContainerFactoryOptions {
        return new class($this->activeProfiles) implements ContainerFactoryOptions {
            public function __construct(
                private readonly Profiles $activeProfiles,
            ) {
            }

            public function profiles(): Profiles {
                return $this->activeProfiles;
            }
        };
    }
}
