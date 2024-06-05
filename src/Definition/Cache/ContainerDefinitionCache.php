<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition\Cache;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

interface ContainerDefinitionCache {

    public function set(CacheKey $cacheKey, ContainerDefinition $containerDefinition) : void;

    public function get(CacheKey $cacheKey) : ?ContainerDefinition;

    public function remove(CacheKey $cacheKey) : void;
}
