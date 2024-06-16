<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Input\Input;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;

final class CacheClearCommand implements Command {

    public function __construct(
        private readonly ContainerDefinitionCache $cache,
        private readonly ContainerDefinitionAnalysisOptions $analysisOptions
    ) {
    }

    public function name() : string {
        return 'cache-clear';
    }

    public function summary() : string {
        return 'Remove any cached ContainerDefinition, forcing static analysis to run again';
    }

    public function help() : string {
        $summary = $this->summary();
        return <<<SHELL
NAME

    cache-clear - $summary
    
SYNOPSIS

    <bold>cache-clear</bold> [OPTION]...

DESCRIPTION

    <bold>cache-clear</bold> ensures that a ContainerDefinition previously compiled
    with build, or by bootstrapping your app, is removed from a configured cache. 
    This ensures the next time your ContainerDefinition is compiled it runs static 
    analysis again.
    
    If you do not have a cacheDir configured this command will error.

OPTIONS

    --config-file="file-path.xml"

        Set the name of the configuration file to be used. If not provided the
        default value will be "annotated-container.xml".

SHELL;
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        $cacheKey = CacheKey::fromContainerDefinitionAnalysisOptions($this->analysisOptions);
        $this->cache->remove($cacheKey);

        $output->stdout->write('<fg:green>Annotated Container cache has been cleared.</fg:green>');
        return 0;
    }
}
