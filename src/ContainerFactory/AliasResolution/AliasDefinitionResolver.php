<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\AliasResolution;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\Reflection\Type;

interface AliasDefinitionResolver {

    public function resolveAlias(
        ContainerDefinition $containerDefinition,
        Profiles $profiles,
        Type $abstractService
    ) : AliasDefinitionResolution;
}
