<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Input\Input;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\CacheAwareContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;

final class BuildCommand implements Command {


    public function __construct(
        private readonly ContainerDefinitionCache $cache,
        private readonly ContainerDefinitionAnalysisOptions $analysisOptions
    ) {
    }

    public function name() : string {
        return 'build';
    }

    public function summary() : string {
        return 'Analyze and cache a ContainerDefinition according to your annotated-container CLI script.';
    }

    public function help() : string {
        $summary = $this->summary();
        return <<<SHELL
NAME

    build - $summary
    
SYNOPSIS

    <bold>build</bold>

DESCRIPTION

    <bold>build</bold> will analyze and cache a ContainerDefinition based on the 
    configuration file provided in your annotated-container CLI script. Using the 
    ContainerDefinitionCache provided in this script the following actions will  
    be performed:
    
    - A CacheKey will be generated based on the configuration's scan directories 
    and DefinitionProvider, if present.
    - Whatever entry for the generated CacheKey will be removed from the provided 
    ContainerDefinitionCache.
    - A CacheAwareContainerDefinitionAnalyzer, using an AnnotatedTargetContainerDefinitionAnalyzer 
    as its delegate, will analyze and cache your ContainerDefinition according to the 
    configuration provided.

SHELL;
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        $cacheKey = CacheKey::fromContainerDefinitionAnalysisOptions($this->analysisOptions);

        $this->cache->remove($cacheKey);
        $this->analyzer()->analyze($this->analysisOptions);

        $output->stdout->write('<fg:green>Successfully built and cached your Container!</fg:green>');

        return 0;
    }

    private function analyzer() : ContainerDefinitionAnalyzer {
        return new CacheAwareContainerDefinitionAnalyzer(
            new AnnotatedTargetContainerDefinitionAnalyzer(
                new PhpParserAnnotatedTargetParser(),
                new Emitter()
            ),
            $this->cache,
        );
    }
}
