<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Unit\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvent;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainer\Unit\Helper\StubAnalysisListener;
use Cspray\AnnotatedContainerFixture\Fixture;
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

        $emitter->addBeforeContainerAnalysisListener($this->stubAnalysisListener);
        $emitter->addAnalyzedContainerDefinitionFromCacheListener($this->stubAnalysisListener);
        $emitter->addAnalyzedInjectDefinitionFromAttributeListener($this->stubAnalysisListener);
        $emitter->addAnalyzedServiceDefinitionFromAttributeListener($this->stubAnalysisListener);
        $emitter->addAnalyzedServiceDelegateDefinitionFromAttributeListener($this->stubAnalysisListener);
        $emitter->addAnalyzedServicePrepareDefinitionFromAttributeListener($this->stubAnalysisListener);
        $emitter->addAddedAliasDefinitionListener($this->stubAnalysisListener);
        $emitter->addAddedInjectDefinitionFromApiListener($this->stubAnalysisListener);
        $emitter->addAddedServiceDefinitionFromApiListener($this->stubAnalysisListener);
        $emitter->addAddedServiceDelegateDefinitionFromApiListener($this->stubAnalysisListener);
        $emitter->addAddedServicePrepareDefinitionFromApiListener($this->stubAnalysisListener);
        $emitter->addAfterContainerAnalysisListener($this->stubAnalysisListener);

        $this->analyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter(),
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
        $this->assertEmittedEvents($this->stubAnalysisListener->getTriggeredEvents());
    }

    protected function getDefinitionProvider() : ?DefinitionProvider {
        return null;
    }

    final protected function getSubject() : ContainerDefinition {
        return $this->analyzer->analyze($this->builder->build());
    }
}
