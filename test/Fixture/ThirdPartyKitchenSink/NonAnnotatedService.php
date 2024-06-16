<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\ThirdPartyKitchenSink;

class NonAnnotatedService implements NonAnnotatedInterface {

    private bool $initCalled = false;

    private function __construct(
        public readonly string $value
    ) {
    }

    public function init() {
        $this->initCalled = true;
    }

    public static function create(string $value) : self {
        return new self($value);
    }

    public function isInitCalled() : bool {
        return $this->initCalled;
    }
}
