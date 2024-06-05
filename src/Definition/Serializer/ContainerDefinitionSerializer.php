<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition\Serializer;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Exception\MismatchedContainerDefinitionSerializerVersions;
use PhpParser\Node\Stmt\Continue_;

interface ContainerDefinitionSerializer {

    public function serialize(ContainerDefinition $containerDefinition) : SerializedContainerDefinition;

    /**
     * @throws MismatchedContainerDefinitionSerializerVersions
     */
    public function deserialize(SerializedContainerDefinition $serializedContainerDefinition) : ContainerDefinition;
}
