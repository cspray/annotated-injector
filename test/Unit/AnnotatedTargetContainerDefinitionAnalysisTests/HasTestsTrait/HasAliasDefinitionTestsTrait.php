<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedAliasDefinition;
use PHPUnit\Framework\Attributes\DataProvider;

trait HasAliasDefinitionTestsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    abstract public static function aliasProvider() : array;

    final public function testExpectedAliasCount() : void {
        $expectedCount = count($this->aliasProvider());

        $this->assertSame($expectedCount, count($this->getSubject()->getAliasDefinitions()));
    }

    #[DataProvider('aliasProvider')]
    final public function testExpectedAliasDefinition(ExpectedAliasDefinition $expectedAliasDefinition) : void {
        $concreteDefinitionsMatchingAbstract = array_filter(
            $this->getSubject()->getAliasDefinitions(),
            fn(AliasDefinition $aliasDefinition) => $aliasDefinition->abstractService() === $expectedAliasDefinition->abstractType
        );
        $concreteTypes = array_map(fn(AliasDefinition $aliasDefinition) => $aliasDefinition->concreteService(), $concreteDefinitionsMatchingAbstract);

        $this->assertContains($expectedAliasDefinition->concreteType, $concreteTypes);
    }
}
