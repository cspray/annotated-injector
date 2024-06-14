<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class AssertExpectedInjectDefinition {

    public function __construct(
        private readonly TestCase $testCase
    ) {
    }

    public function assert(ExpectedInject $expectedInject, ContainerDefinition $containerDefinition) : void {
        $definitions = $this->getDefinitionsForService($expectedInject, $containerDefinition);
        $definitions = $this->filterMethodName($expectedInject, $definitions);
        $definitions = $this->filterMethodParameter($expectedInject, $definitions);

        $this->validateMethodType($expectedInject, $definitions);
        $this->validateValue($expectedInject, $definitions);
        $this->validateProfiles($expectedInject, $definitions);
        $this->validateStoreName($expectedInject, $definitions);

        $this->testCase->addToAssertionCount(1);
    }

    private function getDefinitionsForService(ExpectedInject $expectedInject, ContainerDefinition $containerDefinition) : array {
        $definitionsForService = array_filter($containerDefinition->injectDefinitions(), fn($injectDefinition) => $injectDefinition->class() === $expectedInject->service);
        if (empty($definitionsForService)) {
            Assert::fail(sprintf(
                'Could not find an InjectDefinition for %s in the provided ContainerDefinition.',
                $expectedInject->service
            ));
        }
        return $definitionsForService;
    }

    private function filterMethodName(ExpectedInject $expectedInject, array $injectDefinitions) : array {
        $definitionsForInjectTarget = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->methodName() === $expectedInject->methodName);
        if (empty($definitionsForInjectTarget)) {
            Assert::fail(sprintf(
                'Could not find an InjectDefinition for method %s::%s.',
                $expectedInject->service,
                $expectedInject->methodName
            ));
        }
        return $definitionsForInjectTarget;
    }

    private function filterMethodParameter(ExpectedInject $expectedInject, array $injectDefinitions) : array {
        $definitionsForParam = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->parameterName() === $expectedInject->targetName);
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
        $definitionsWithTypes = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->type() === $expectedInject->type);
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
        $definitionsWithType = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->type() === $expectedInject->type);
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
        $definitionsWithValues = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->value() === $expectedInject->value);
        if (empty($definitionsWithValues)) {
            $message = sprintf(
                'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s with a value matching:%s %s.',
                $expectedInject->targetName,
                $expectedInject->service,
                $expectedInject->methodName,
                str_repeat(PHP_EOL, 2),
                var_export($expectedInject->value, true)
            );
            Assert::fail($message);
        }
    }

    private function validateProfiles(ExpectedInject $expectedInject, array $injectDefinitions) : void {
        $definitionsWithProfiles = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->profiles() === $expectedInject->profiles);
        $profileDescriptor = fn() => (empty($expectedInject->profiles) ?
            'no profiles' :
            'profiles: ' . join(', ', array_map(fn($profile) => "'$profile'", $expectedInject->profiles)));
        if (empty($definitionsWithProfiles)) {
            $message = sprintf(
                'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s with %s.',
                $expectedInject->targetName,
                $expectedInject->service,
                $expectedInject->methodName,
                $profileDescriptor()
            );
            Assert::fail($message);
        }
    }

    private function validateStoreName(ExpectedInject $expectedInject, array $injectDefinitions) : void {
        $definitionsWithNames = array_filter($injectDefinitions, fn($injectDefinition) => $injectDefinition->storeName() === $expectedInject->store);
        $storeDescriptor = fn() => ($expectedInject->store === null ? 'no store name' : 'store name: \'' . $expectedInject->store . '\'');
        if (empty($definitionsWithNames)) {
            $message = sprintf(
                'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s with %s.',
                $expectedInject->targetName,
                $expectedInject->service,
                $expectedInject->methodName,
                $storeDescriptor()
            );
            Assert::fail($message);
        }
    }
}
