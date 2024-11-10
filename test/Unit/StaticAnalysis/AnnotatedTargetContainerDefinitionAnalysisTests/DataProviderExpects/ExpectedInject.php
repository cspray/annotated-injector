<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects;

use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;

final class ExpectedInject {

    private function __construct(
        public readonly Type $service,
        public readonly string $tarname,
        public readonly mixed $value,
        public readonly Type|TypeUnion $type,
        public readonly ?string $methodName = null,
        public readonly array $profiles = [],
        public readonly ?string $store = null
    ) {
    }

    public static function forConstructParam(Type $service, string $param, Type|TypeUnion $type, mixed $value, array $profiles = ['default'], ?string $store = null) : ExpectedInject {
        return self::forMethodParam($service, '__construct', $param, $type, $value, $profiles, $store);
    }

    public static function forMethodParam(Type $service, string $method, string $param, Type|TypeUnion $type, mixed $value, array $profiles = ['default'], ?string $store = null) : ExpectedInject {
        return new self($service, $param, $value, $type, $method, $profiles, $store);
    }
}
