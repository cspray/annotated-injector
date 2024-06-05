<?php

namespace Cspray\AnnotatedContainer\StaticAnalysis;

final class CompositeDefinitionProvider implements DefinitionProvider, \Stringable {

    /**
     * @var list<DefinitionProvider>
     */
    private readonly array $providers;

    public function __construct(
        DefinitionProvider $provider,
        DefinitionProvider...$providers
    ) {
        $this->providers = array_values([
            $provider,
            ...$providers
        ]);
    }

    public function consume(DefinitionProviderContext $context) : void {
        foreach ($this->providers as $provider) {
            $provider->consume($context);
        }
    }

    public function getDefinitionProviders() : array {
        return $this->providers;
    }

    public function __toString() : string {
        $classes = array_map(
            static fn(DefinitionProvider $provider) => $provider::class,
            $this->getDefinitionProviders()
        );
        return sprintf('Composite<%s>', implode(', ', $classes));
    }
}
