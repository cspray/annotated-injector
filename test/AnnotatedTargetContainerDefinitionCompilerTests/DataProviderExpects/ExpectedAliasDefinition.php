<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetContainerDefinitionCompilerTests\DataProviderExpects;

use Cspray\Typiphy\ObjectType;

final class ExpectedAliasDefinition {

    public function __construct(
        public readonly ObjectType $abstractType,
        public readonly ObjectType $concreteType
    ) {}

}