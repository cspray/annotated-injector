<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Unit\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

abstract class AnnotatedTargetContainerDefinitionCompilerTestCase extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private AnnotatedTargetContainerDefinitionAnalyzer $analyzer;

    private ContainerDefinitionAnalysisOptionsBuilder $builder;

    /**
     * @return Fixture[]|Fixture
     */
    abstract protected function getFixtures() : array|Fixture;

    protected function setUp() : void {
        $this->analyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
        );

        $fixtures = $this->getFixtures();
        if (!is_array($fixtures)) {
            $fixtures = [$fixtures];
        }
        $dirs = [];
        foreach ($fixtures as $fixture) {
            $dirs[] = $fixture->getPath();
        }

        $this->builder = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(...$dirs);
        $consumer = $this->getDefinitionProvider();
        if (!is_null($consumer)) {
            $this->builder = $this->builder->withDefinitionProvider($consumer);
        }
    }

    protected function getDefinitionProvider() : ?DefinitionProvider {
        return null;
    }

    final protected function getSubject() : ContainerDefinition {
        return $this->analyzer->analyze($this->builder->build());
    }
}
