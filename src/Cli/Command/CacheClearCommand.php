<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\DefaultDefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefaultParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\XmlBootstrappingConfiguration;
use Cspray\AnnotatedContainer\Cli\Command;
use Cspray\AnnotatedContainer\Cli\Exception\CacheDirConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\CacheDirNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\ConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Exception\InvalidOptionType;
use Cspray\AnnotatedContainer\Cli\Input;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;

final class CacheClearCommand implements Command {

    public function __construct(
        private readonly BootstrappingDirectoryResolver $directoryResolver
    ) {
    }

    public function getName() : string {
        return 'cache-clear';
    }

    public function getHelp() : string {
        return <<<SHELL
NAME

    cache-clear - Remove cached ContainerDefinition, forcing rebuild of your Container
    
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
        $configName = $input->getOption('config-file');
        if (!isset($configName)) {
            // This not being present would be highly irregular and not party of the happy path
            // But it is possible that somebody created the configuration manually and is not using composer
            $composerFile = $this->directoryResolver->getConfigurationPath('composer.json');
            if (file_exists($composerFile)) {
                $composer = json_decode(file_get_contents($composerFile), true);
                $configName = $composer['extra']['annotatedContainer']['configFile'] ?? 'annotated-container.xml';
            } else {
                $configName = 'annotated-container.xml';
            }
        } else {
            if (is_bool($configName)) {
                throw InvalidOptionType::fromBooleanOption('config-file');
            } elseif (is_array($configName)) {
                throw InvalidOptionType::fromArrayOption('config-file');
            }
        }

        $configPath = $this->directoryResolver->getConfigurationPath($configName);
        if (!file_exists($configPath)) {
            throw ConfigurationNotFound::fromMissingFile($configName);
        }

        $config = new XmlBootstrappingConfiguration($configPath, new DefaultParameterStoreFactory(), new DefaultDefinitionProviderFactory());
        $cacheDir = $config->cacheDirectory();
        if (!isset($cacheDir)) {
            throw CacheDirConfigurationNotFound::fromCacheCommand();
        }

        $cachePath = $this->directoryResolver->getCachePath($cacheDir);
        if (!is_dir($cachePath)) {
            throw CacheDirNotFound::fromMissingDirectory($cacheDir);
        }

        $sourceDirs = [];
        foreach ($config->scanDirectories() as $scanDirectory) {
            $sourceDirs[] = $this->directoryResolver->getPathFromRoot($scanDirectory);
        }

        sort($sourceDirs);
        $cacheKey = md5(join($sourceDirs));
        $cachePath = $this->directoryResolver->getCachePath(sprintf('%s/%s', $cacheDir, $cacheKey));

        if (file_exists($cachePath)) {
            unlink($cachePath);
        }

        $output->stdout->write('<fg:green>Annotated Container cache has been cleared.</fg:green>');
        return 0;
    }
}
