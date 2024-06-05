<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition\Serializer;

use DOMDocument;

final class SerializedContainerDefinition {

    /**
     * @param non-empty-string $contents
     */
    private function __construct(
        private readonly string $contents
    ) {
    }

    /**
     * @param non-empty-string $contents
     * @return self
     */
    public static function fromString(string $contents) : self {
        return new self($contents);
    }

    /**
     * @return non-empty-string
     */
    public function asString() : string {
        return $this->contents;
    }
}
