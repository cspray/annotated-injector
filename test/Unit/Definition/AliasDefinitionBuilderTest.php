<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Definition;

use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;

class AliasDefinitionBuilderTest extends TestCase {

    public function testWithConcreteImmutableBuilder() {
        $abstract = Fixtures::implicitAliasedServices()->fooInterface();
        $concrete = Fixtures::implicitAliasedServices()->fooImplementation();
        $builder1 = AliasDefinitionBuilder::forAbstract($abstract);
        $builder2 = $builder1->withConcrete($concrete);

        $this->assertNotSame($builder1, $builder2);
    }

    public function testWithConcreteReturnsCorrectServiceDefinitions() {
        $abstract = Fixtures::implicitAliasedServices()->fooInterface();
        $concrete = Fixtures::implicitAliasedServices()->fooImplementation();
        $aliasDefinition = AliasDefinitionBuilder::forAbstract($abstract)
            ->withConcrete($concrete)
            ->build();

        $this->assertSame($abstract, $aliasDefinition->abstractService());
        $this->assertSame($concrete, $aliasDefinition->concreteService());
    }
}
