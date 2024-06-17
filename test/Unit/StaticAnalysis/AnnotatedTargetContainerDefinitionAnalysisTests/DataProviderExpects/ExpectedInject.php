<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects;

use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeUnion;

final class ExpectedInject {

    private function __construct(
        public readonly ObjectType $service,
        public readonly string $tarname,
        public readonly mixed $value,
        public readonly Type|TypeUnion $type,
        public readonly ?string $methodName = null,
        public readonly array $profiles = [],
        public readonly ?string $store = null
    ) {
    }

    public static function forConstructParam(ObjectType $service, string $param, Type|TypeUnion $type, mixed $value, array $profiles = ['default'], ?string $store = null) : ExpectedInject {
        return self::forMethodParam($service, '__construct', $param, $type, $value, $profiles, $store);
    }

    public static function forMethodParam(ObjectType $service, string $method, string $param, Type|TypeUnion $type, mixed $value, array $profiles = ['default'], ?string $store = null) : ExpectedInject {
        return new self($service, $param, $value, $type, $method, $profiles, $store);
    }
}
