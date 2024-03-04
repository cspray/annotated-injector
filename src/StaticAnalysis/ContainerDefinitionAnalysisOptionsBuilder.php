<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;

/**
 * The preferred method for constructing ContainerDefinitionCompileOptions
 */
final class ContainerDefinitionAnalysisOptionsBuilder {

    /** @var list<string> */
    private array $directories = [];

    private ?DefinitionProvider $consumer = null;

    private function __construct() {
    }

    /**
     * Specify the directories that should be parsed when generating the ContainerDefinition
     *
     * @param string ...$directories
     * @return static
     */
    public static function scanDirectories(string...$directories) : self {
        $instance = new self();
        $instance->directories = $directories;
        return $instance;
    }

    /**
     * Specify that the ContainerDefinitionBuilder should be modified before the ContainerDefinition is built.
     *
     * @param DefinitionProvider $consumer
     * @return $this
     */
    #[SingleEntrypointDefinitionProvider]
    public function withDefinitionProvider(DefinitionProvider $consumer) : self {
        $instance = clone $this;
        $instance->consumer = $consumer;
        return $instance;
    }

    public function build() : ContainerDefinitionAnalysisOptions {
        return new class(
            $this->directories,
            $this->consumer,
        ) implements ContainerDefinitionAnalysisOptions {
            public function __construct(
                private readonly array               $directories,
                private readonly ?DefinitionProvider $consumer,
            ) {
            }

            public function getScanDirectories(): array {
                return $this->directories;
            }

            public function getDefinitionProvider(): ?DefinitionProvider {
                return $this->consumer;
            }
        };
    }
}
