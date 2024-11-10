<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Unit\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainer\Unit\Helper\StubAnalysisListener;
use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\TestCase;

abstract class AnnotatedTargetContainerDefinitionAnalyzerTestCase extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    private AnnotatedTargetContainerDefinitionAnalyzer $analyzer;

    private ContainerDefinitionAnalysisOptionsBuilder $builder;

    private StubAnalysisListener $stubAnalysisListener;

    /**
     * @return Fixture[]|Fixture
     */
    abstract protected function getFixtures() : array|Fixture;

    abstract protected function assertEmittedEvents(AnalysisEventCollection $analysisEventCollection) : void;

    protected function setUp() : void {
        $this->stubAnalysisListener = new StubAnalysisListener();

        $emitter = new Emitter();

        $emitter->addListener($this->stubAnalysisListener);

        $this->analyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            $emitter,
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

    public function testAppropriateEventsEmitted() : void {
        $this->getSubject();
        $this->assertEmittedEvents($this->stubAnalysisListener->getTriggeredEvents());
    }

    protected function getDefinitionProvider() : ?DefinitionProvider {
        return null;
    }

    final protected function getSubject() : ContainerDefinition {
        return $this->analyzer->analyze($this->builder->build());
    }
}
