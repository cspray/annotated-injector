<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Definition;

use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Unit\Helper\HasMockDefinitions;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\AssertExpectedInjectDefinition;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use function Cspray\AnnotatedContainer\Reflection\types;

class AssertExpectedInjectDefinitionTest extends TestCase {

    use HasMockDefinitions;

    private function getArrayConstructorExpectedInject(array $profiles = ['default'], string $store = null) : ExpectedInject {
        return ExpectedInject::forConstructParam(
            Fixtures::injectConstructorServices()->injectArrayService(),
            'values',
            types()->array(),
            ['dependency', 'injection', 'rocks'],
            profiles: $profiles,
            store: $store
        );
    }

    public function testExpectedInjectServiceNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for %s in the provided ContainerDefinition.',
            Fixtures::injectConstructorServices()->injectArrayService()->name(),
        ));

        $assertion->assert(
            $this->getArrayConstructorExpectedInject(),
            ContainerDefinitionBuilder::newDefinition()->build()
        );
    }

    public function testExpectedInjectMethodNameNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    'notConstruct',
                    'values',
                    types()->array(),
                    [1, 2, 3]
                )
            ]
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for method %s::%s.',
            Fixtures::injectConstructorServices()->injectArrayService()->name(),
            '__construct'
        ));

        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'notValues',
                    types()->array(),
                    ['dependency', 'injection', 'rocks'],
                )
            ]
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s.',
            'values',
            Fixtures::injectConstructorServices()->injectArrayService()->name(),
            '__construct'
        ));

        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamWrongType() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'values',
                    types()->string(),
                    'a string'
                )
            ]
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with type \'array\'.',
            Fixtures::injectConstructorServices()->injectArrayService()->name(),
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamWrongValues() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'values',
                    types()->array(),
                    ['service', 'registry', 'boo']
                )
            ]
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with a value matching:%s %s',
            Fixtures::injectConstructorServices()->injectArrayService()->name(),
            str_repeat(PHP_EOL, 2),
            var_export(['dependency', 'injection', 'rocks'], true)
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamWrongProfiles() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'values',
                    types()->array(),
                    ['dependency', 'injection', 'rocks'],
                    profiles: ['foo']
                )
            ]
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with profiles: \'default\'.',
            Fixtures::injectConstructorServices()->injectArrayService()->name()
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamWrongProfilesAlternateMessage() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'values',
                    types()->array(),
                    ['dependency', 'injection', 'rocks'],
                )
            ]
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with profiles: \'foo\', \'bar\'.',
            Fixtures::injectConstructorServices()->injectArrayService()->name(),
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(profiles: ['foo', 'bar']), $containerDefinition);
    }

    public function testExpectedInjectWrongStoreName() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'values',
                    types()->array(),
                    ['dependency', 'injection', 'rocks'],
                    store: 'store-name'
                )
            ]
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with no store name.',
            Fixtures::injectConstructorServices()->injectArrayService()->name(),
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectWrongStoreNameAlternateMessage() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'values',
                    types()->array(),
                    ['dependency', 'injection', 'rocks'],
                )
            ]
        );

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with store name: \'foo-store\'.',
            Fixtures::injectConstructorServices()->injectArrayService()->name()
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(store: 'foo-store'), $containerDefinition);
    }

    public function testFoundInjectDefinitionIncreasesAssertionCount() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'values',
                    types()->array(),
                    ['dependency', 'injection', 'rocks'],
                )
            ]
        );

        $beforeCount = $this->numberOfAssertionsPerformed();

        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);

        $this->assertSame($beforeCount + 1, $this->numberOfAssertionsPerformed());
    }

    public function testFoundInjectDefinitionWithCustomStoreIncreasesAssertionCount() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'values',
                    types()->array(),
                    ['dependency', 'injection', 'rocks'],
                    store: 'foo-store'
                )
            ]
        );

        $beforeCount = $this->numberOfAssertionsPerformed();

        $assertion->assert($this->getArrayConstructorExpectedInject(store: 'foo-store'), $containerDefinition);

        $this->assertSame($beforeCount + 1, $this->numberOfAssertionsPerformed());
    }

    public function testFoundInjectDefinitionWithCustomProfilesIncreasesAssertionCount() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = $this->containerDefinition(
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectArrayService(),
                    '__construct',
                    'values',
                    types()->array(),
                    ['dependency', 'injection', 'rocks'],
                    profiles: ['foo', 'bar']
                )
            ]
        );

        $beforeCount = $this->numberOfAssertionsPerformed();

        $assertion->assert($this->getArrayConstructorExpectedInject(profiles: ['foo', 'bar']), $containerDefinition);

        $this->assertSame($beforeCount + 1, $this->numberOfAssertionsPerformed());
    }
}
