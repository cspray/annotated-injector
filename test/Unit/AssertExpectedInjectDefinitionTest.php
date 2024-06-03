<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\AssertExpectedInjectDefinition;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\stringType;

class AssertExpectedInjectDefinitionTest extends TestCase {

    private function getArrayConstructorExpectedInject(array $profiles = ['default'], string $store = null) : ExpectedInject {
        return ExpectedInject::forConstructParam(
            Fixtures::injectConstructorServices()->injectArrayService(),
            'values',
            arrayType(),
            ['dependency', 'injection', 'rocks'],
            profiles: $profiles,
            store: $store
        );
    }

    private function getStringPropertyExpectedInject(array $profiles = ['default'], string $store = null) : ExpectedInject {
        return ExpectedInject::forClassProperty(
            Fixtures::configurationServices()->myConfig(),
            'key',
            stringType(),
            'my-api-key',
            profiles: $profiles,
            store: $store
        );
    }

    public function testExpectedInjectServiceNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for %s in the provided ContainerDefinition.',
            Fixtures::injectConstructorServices()->injectArrayService()
        ));

        $assertion->assert(
            $this->getArrayConstructorExpectedInject(),
            ContainerDefinitionBuilder::newDefinition()->build()
        );
    }

    public function testExpectedInjectConstructorParamTargetTypeNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withProperty(arrayType(), 'propName')
                    ->withValue(['a', 'b', 'c'])
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for %s that is a MethodParameter injection.',
            Fixtures::injectConstructorServices()->injectArrayService()
        ));

        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodNameNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('notConstruct', arrayType(), 'values')
                    ->withValue([1, 2, 3])
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for method %s::%s.',
            Fixtures::injectConstructorServices()->injectArrayService(),
            '__construct'
        ));

        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', arrayType(), 'notValues')
                    ->withValue(['dependency', 'injection', 'rocks'])
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'%s\' on method %s::%s.',
            'values',
            Fixtures::injectConstructorServices()->injectArrayService(),
            '__construct'
        ));

        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamWrongType() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', stringType(), 'values')
                    ->withValue('a string')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with type \'array\'.',
            Fixtures::injectConstructorServices()->injectArrayService(),
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamWrongValues() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', arrayType(), 'values')
                    ->withValue(['service', 'registry', 'booo'])
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with a value matching:%s %s',
            Fixtures::injectConstructorServices()->injectArrayService(),
            str_repeat(PHP_EOL, 2),
            var_export(['dependency', 'injection', 'rocks'], true)
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamWrongProfiles() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', arrayType(), 'values')
                    ->withValue(['dependency', 'injection', 'rocks'])
                    ->withProfiles('foo')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with profiles: \'default\'.',
            Fixtures::injectConstructorServices()->injectArrayService()
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectMethodParamWrongProfilesAlternateMessage() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', arrayType(), 'values')
                    ->withValue(['dependency', 'injection', 'rocks'])
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with profiles: \'foo\', \'bar\'.',
            Fixtures::injectConstructorServices()->injectArrayService()
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(profiles: ['foo', 'bar']), $containerDefinition);
    }

    public function testExpectedInjectWrongStoreName() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', arrayType(), 'values')
                    ->withValue(['dependency', 'injection', 'rocks'])
                    ->withStore('store-name')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with no store name.',
            Fixtures::injectConstructorServices()->injectArrayService()
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectWrongStoreNameAlternateMessage() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', arrayType(), 'values')

                    ->withValue(['dependency', 'injection', 'rocks'])
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for parameter \'values\' on method %s::__construct with store name: \'foo-store\'.',
            Fixtures::injectConstructorServices()->injectArrayService()
        ));
        $assertion->assert($this->getArrayConstructorExpectedInject(store: 'foo-store'), $containerDefinition);
    }

    public function testFoundInjectDefinitionIncreasesAssertionCount() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', arrayType(), 'values')
                    ->withValue(['dependency', 'injection', 'rocks'])
                    ->build()
            )->build();

        $beforeCount = $this->numberOfAssertionsPerformed();

        $assertion->assert($this->getArrayConstructorExpectedInject(), $containerDefinition);

        $this->assertSame($beforeCount + 1, $this->numberOfAssertionsPerformed());
    }

    public function testFoundInjectDefinitionWithCustomStoreIncreasesAssertionCount() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', arrayType(), 'values')
                    ->withValue(['dependency', 'injection', 'rocks'])
                    ->withStore('foo-store')
                    ->build()
            )->build();

        $beforeCount = $this->numberOfAssertionsPerformed();

        $assertion->assert($this->getArrayConstructorExpectedInject(store: 'foo-store'), $containerDefinition);

        $this->assertSame($beforeCount + 1, $this->numberOfAssertionsPerformed());
    }

    public function testFoundInjectDefinitionWithCustomProfilesIncreasesAssertionCount() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectArrayService())
                    ->withMethod('__construct', arrayType(), 'values')
                    ->withValue(['dependency', 'injection', 'rocks'])
                    ->withProfiles('foo', 'bar')
                    ->build()
            )->build();

        $beforeCount = $this->numberOfAssertionsPerformed();

        $assertion->assert($this->getArrayConstructorExpectedInject(profiles: ['foo', 'bar']), $containerDefinition);

        $this->assertSame($beforeCount + 1, $this->numberOfAssertionsPerformed());
    }

    public function testExpectedInjectConfigurationNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for %s in the provided ContainerDefinition.',
            Fixtures::configurationServices()->myConfig()
        ));

        $assertion->assert(
            $this->getStringPropertyExpectedInject(),
            ContainerDefinitionBuilder::newDefinition()->build()
        );
    }

    public function testExpectedInjectPropertyTargetTypeNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withMethod('__construct', arrayType(), 'val')
                    ->withValue(['a', 'b', 'c'])
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for %s that is a ClassProperty injection.',
            Fixtures::configurationServices()->myConfig()
        ));

        $assertion->assert($this->getStringPropertyExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectPropertyNameNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'notFound')
                    ->withValue('my-api-key')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf('Could not find an InjectDefinition for property %s::key.', Fixtures::configurationServices()->myConfig()));
        $assertion->assert($this->getStringPropertyExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectPropertyTypeNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(intType(), 'key')
                    ->withValue('my-api-key')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for property \'key\' on %s with type \'string\'.',
            Fixtures::configurationServices()->myConfig()
        ));
        $assertion->assert($this->getStringPropertyExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectPropertyValueNotFound() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('bad-api-key')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for property \'key\' on %s with value matching:%s \'my-api-key\'.',
            Fixtures::configurationServices()->myConfig(),
            str_repeat(PHP_EOL, 2)
        ));
        $assertion->assert($this->getStringPropertyExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectPropertyNoProfiles() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('my-api-key')
                    ->withProfiles('foo')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for property \'key\' on %s with profiles: \'default\'.',
            Fixtures::configurationServices()->myConfig()
        ));
        $assertion->assert($this->getStringPropertyExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectPropertyWrongProfilesAlternateMessage() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('my-api-key')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for property \'key\' on %s with profiles: \'bar\', \'foo\'.',
            Fixtures::configurationServices()->myConfig()
        ));
        $assertion->assert($this->getStringPropertyExpectedInject(profiles: ['bar', 'foo']), $containerDefinition);
    }

    public function testExpectedInjectPropertyNoStore() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('my-api-key')
                    ->withStore('foo')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for property \'key\' on %s with no store name.',
            Fixtures::configurationServices()->myConfig()
        ));
        $assertion->assert($this->getStringPropertyExpectedInject(), $containerDefinition);
    }

    public function testExpectedInjectPropertyAlternateStoreMessage() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('my-api-key')
                    ->build()
            )->build();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage(sprintf(
            'Could not find an InjectDefinition for property \'key\' on %s with store name: \'foo\'.',
            Fixtures::configurationServices()->myConfig()
        ));
        $assertion->assert($this->getStringPropertyExpectedInject(store: 'foo'), $containerDefinition);
    }

    public function testGoodExpectedInjectPropertyAddsToAssertionCount() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('my-api-key')
                    ->build()
            )->build();

        $beforeCount = $this->numberOfAssertionsPerformed();

        $assertion->assert($this->getStringPropertyExpectedInject(), $containerDefinition);

        $this->assertSame($beforeCount + 1, $this->numberOfAssertionsPerformed());
    }

    public function testGoodExpectedInjectPropertyWithCustomStoreAddsToAssertionCount() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('my-api-key')
                    ->withStore('foo-store')
                    ->build()
            )->build();

        $beforeCount = $this->numberOfAssertionsPerformed();

        $assertion->assert($this->getStringPropertyExpectedInject(store: 'foo-store'), $containerDefinition);

        $this->assertSame($beforeCount + 1, $this->numberOfAssertionsPerformed());
    }

    public function testGoodExpectedInjectPropertyWithCustomProfilesAddsToAssertionCount() : void {
        $assertion = new AssertExpectedInjectDefinition($this);
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('my-api-key')
                    ->withProfiles('foo', 'bar')
                    ->build()
            )->build();

        $beforeCount = $this->numberOfAssertionsPerformed();

        $assertion->assert($this->getStringPropertyExpectedInject(profiles: ['foo', 'bar']), $containerDefinition);

        $this->assertSame($beforeCount + 1, $this->numberOfAssertionsPerformed());
    }
}
