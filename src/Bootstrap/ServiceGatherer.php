<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

interface ServiceGatherer {

    /**
     * @template T
     * @param class-string<T> $type
     * @return list<ServiceFromServiceDefinition<T>>
     */
    public function servicesForType(string $type) : array;

    /**
     * @template T
     * @param class-string<T> $attributeType
     * @return list<ServiceFromServiceDefinition<T>>
     */
    public function servicesWithAttribute(string $attributeType) : array;
}
