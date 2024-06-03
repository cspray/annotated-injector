<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\InjectTargetType;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class AssertExpectedInjectDefinition {

    public function __construct(
        private readonly TestCase $testCase
    ) {
    }

    public function assert(ExpectedInject $expectedInject, ContainerDefinition $containerDefinition) : void {
        $definitions = $this->getDefinitionsForService($expectedInject, $containerDefinition);
        $definitions = $this->filterTargetType($expectedInject, $definitions);
        if ($expectedInject->injectTargetType === InjectTargetType::MethodParameter) {
            $definitions = $this->filterMethodName($expectedInject, $definitions);
            $definitions = $this->filterMethodParameter($expectedInject, $definitions);
            $this->validateMethodType($expectedInject, $definitions);
        } elseif ($expectedInject->injectTargetType === InjectTargetType::ClassProperty) {
            $definitions = $this->filterPropertyName($expectedInject, $definitions);
            $this->validatePropertyType($expectedInject, $definitions);
        }

        $this->validateValue($expectedInject, $definitions);
        $this->validateProfiles($expectedInject, $definitions);
        $this->validateStoreName($expectedInject, $definitions);

        $this->testCase->addToAssertionCount(1);
    }

    private function getDefinitionsForService(ExpectedInject $expectedInject, ContainerDefinition $containerDefinition) : array {
        $definitionsForService = array_filter($containerDefinition->getInjectDefinitions(), fn($injectDefinition) => $injectDefinition->getTargetIdentifier()->getClass() === $expectedInject->service);
        if (empty($definitionsForService)) {
            Assert::fail(sprintf(
                'Could not find an InjectDefinition for %s in the provided ContainerDefinition.',
                $expectedInject->service
            ));
        }
        return $definitionsForService;
    }

    private function filterTargetType(ExpectedInject $expectedInject, array $injectDefinitions) : array {
        $definitionsForTargetType = array_filter($injectDefinitions, fn($injectDefinition) => $expectedInject->injectTargetType->isValidTargetIdentifier($injectDefinition->getTargetIdentifier()));
        if (empty($definitionsForTargetType)) {
            Assert::fail(sprintf(
                'Could not find an InjectDefinition for %s that is a %s injection.',
                $expectedInject->service,
                $expectedInject->injectTargetType->name
            ));
        }
        return $definitionsForTargetType;
    }

    private function filterMethodName(ExpectedInject $expectedInject, array $injectDefinitions) : array {
        $definitionsForInjectTarget = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->getTargetIdentifier()->getMethodName() === $expectedInject->methodName);
        if (empty($definitionsForInjectTarget)) {
            Assert::fail(sprintf(
                'Could not find an InjectDefinition for method %s::%s.',
                $expectedInject->service,
                $expectedInject->methodName
            ));
        }
        return $definitionsForInjectTarget;
    }

    private function filterPropertyName(ExpectedInject $expectedInject, array $injectDefinitions) : array {
        $definitionsForInjectTarget = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->getTargetIdentifier()->getName() === $expectedInject->targetName);
        if (empty($definitionsForInjectTarget)) {
            Assert::fail(sprintf(
                'Could not find an InjectDefinition for property %s::%s.',
                $expectedInject->service,
                $expectedInject->targetName
            ));
        }
        return $definitionsForInjectTarget;
    }

    private function filterMethodParameter(ExpectedInject $expectedInject, array $injectDefinitions) : array {
        $definitionsForParam = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->getTargetIdentifier()->getName() === $expectedInject->targetName);
        if (empty($definitionsForParam)) {
            Assert::fail(sprintf(
                'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s.',
                $expectedInject->targetName,
                $expectedInject->service,
                $expectedInject->methodName
            ));
        }
        return $definitionsForParam;
    }

    private function validateMethodType(ExpectedInject $expectedInject, array $injectDefinitions) : void {
        $definitionsWithTypes = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->getType() === $expectedInject->type);
        if (empty($definitionsWithTypes)) {
            Assert::fail(sprintf(
                'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s with type \'%s\'.',
                $expectedInject->targetName,
                $expectedInject->service,
                $expectedInject->methodName,
                $expectedInject->type
            ));
        }
    }

    private function validatePropertyType(ExpectedInject $expectedInject, array $injectDefinitions) : void {
        $definitionsWithType = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->getType() === $expectedInject->type);
        if (empty($definitionsWithType)) {
            Assert::fail(sprintf(
                'Could not find an InjectDefinition for property \'%s\' on %s with type \'%s\'.',
                $expectedInject->targetName,
                $expectedInject->service,
                $expectedInject->type
            ));
        }
    }

    private function validateValue(ExpectedInject $expectedInject, array $injectDefinitions) : void {
        $definitionsWithValues = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->getValue() === $expectedInject->value);
        if (empty($definitionsWithValues)) {
            $message = '';
            if ($expectedInject->injectTargetType === InjectTargetType::MethodParameter) {
                $message = sprintf(
                    'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s with a value matching:%s %s.',
                    $expectedInject->targetName,
                    $expectedInject->service,
                    $expectedInject->methodName,
                    str_repeat(PHP_EOL, 2),
                    var_export($expectedInject->value, true)
                );
            } elseif ($expectedInject->injectTargetType === InjectTargetType::ClassProperty) {
                $message = sprintf(
                    'Could not find an InjectDefinition for property \'%s\' on %s with value matching:%s %s.',
                    $expectedInject->targetName,
                    $expectedInject->service,
                    str_repeat(PHP_EOL, 2),
                    var_export($expectedInject->value, true)
                );
            }
            Assert::fail($message);
        }
    }

    private function validateProfiles(ExpectedInject $expectedInject, array $injectDefinitions) : void {
        $definitionsWithProfiles = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->getProfiles() === $expectedInject->profiles);
        $profileDescriptor = fn() => (empty($expectedInject->profiles) ?
            'no profiles' :
            'profiles: ' . join(', ', array_map(fn($profile) => "'$profile'", $expectedInject->profiles)));
        if (empty($definitionsWithProfiles)) {
            $message = '';
            if ($expectedInject->injectTargetType === InjectTargetType::MethodParameter) {
                $message = sprintf(
                    'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s with %s.',
                    $expectedInject->targetName,
                    $expectedInject->service,
                    $expectedInject->methodName,
                    $profileDescriptor()
                );
            } elseif ($expectedInject->injectTargetType === InjectTargetType::ClassProperty) {
                $message = sprintf(
                    'Could not find an InjectDefinition for property \'%s\' on %s with %s.',
                    $expectedInject->targetName,
                    $expectedInject->service,
                    $profileDescriptor()
                );
            }
            Assert::fail($message);
        }
    }

    private function validateStoreName(ExpectedInject $expectedInject, array $injectDefinitions) : void {
        $definitionsWithNames = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->getStoreName() === $expectedInject->store);
        $storeDescriptor = fn() => ($expectedInject->store === null ? 'no store name' : 'store name: \'' . $expectedInject->store . '\'');
        if (empty($definitionsWithNames)) {
            $message = '';
            if ($expectedInject->injectTargetType === InjectTargetType::MethodParameter) {
                $message = sprintf(
                    'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s with %s.',
                    $expectedInject->targetName,
                    $expectedInject->service,
                    $expectedInject->methodName,
                    $storeDescriptor()
                );
            } elseif ($expectedInject->injectTargetType === InjectTargetType::ClassProperty) {
                $message = sprintf(
                    'Could not find an InjectDefinition for property \'%s\' on %s with %s.',
                    $expectedInject->targetName,
                    $expectedInject->service,
                    $storeDescriptor()
                );
            }
            Assert::fail($message);
        }
    }
}
