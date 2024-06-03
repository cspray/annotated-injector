<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;

final class DelegatedParameterStoreFactory implements ParameterStoreFactory {

    /**
     * @var array<string|class-string<ParameterStore>, ParameterStoreFactory>
     */
    private array $mappedParameterStores;

    public function __construct(
        private readonly ParameterStoreFactory $defaultFactory
    ) {
        $this->mappedParameterStores = [];
    }

    public function addParameterStoreFactory(string $identifier, ParameterStoreFactory $factory) : void {
        $this->mappedParameterStores[$identifier] = $factory;
    }

    public function createParameterStore(string $identifier) : ParameterStore {
        $factory = $this->mappedParameterStores[$identifier] ?? $this->defaultFactory;
        return $factory->createParameterStore($identifier);
    }
}
