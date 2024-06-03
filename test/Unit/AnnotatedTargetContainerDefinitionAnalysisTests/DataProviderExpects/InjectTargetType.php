<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects;

use Cspray\AnnotatedContainer\Definition\InjectTargetIdentifier;

enum InjectTargetType {
    case MethodParameter;
    case ClassProperty;

    public function isValidTargetIdentifier(InjectTargetIdentifier $injectTargetIdentifier) : bool {
        if ($this === self::MethodParameter) {
            return $injectTargetIdentifier->isMethodParameter();
        } elseif ($this === self::ClassProperty) {
            return $injectTargetIdentifier->isClassProperty();
        }

        return false;
    }
}
