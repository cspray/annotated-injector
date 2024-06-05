<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Closure;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\Bootstrap\DefaultDefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefaultParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\PostAnalysisObserver;
use Cspray\AnnotatedContainer\Cli\Command;
use Cspray\AnnotatedContainer\Cli\Exception\ConfigurationNotFound;
use Cspray\AnnotatedContainer\Cli\Input;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\AfterBootstrap;
use Cspray\AnnotatedContainer\Exception\UnsupportedOperation;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceName;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServicePrepare;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\DuplicateServiceType;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\MultiplePrimaryForAbstractService;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServiceDelegate;
use Cspray\AnnotatedContainer\LogicalConstraint\Check\NonPublicServicePrepare;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraint;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintValidator;
use Cspray\AnnotatedContainer\LogicalConstraint\LogicalConstraintViolationType;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\PrecisionStopwatch\Stopwatch;
use DI\Container;

final class ValidateCommand implements Command {

    /**
     * @var list<LogicalConstraint>
     */
    private readonly array $logicalConstraints;

    private readonly LogicalConstraintValidator $validator;

    public function __construct(
        private readonly BootstrappingDirectoryResolver $directoryResolver
    ) {
        $this->logicalConstraints = [
            new DuplicateServiceDelegate(),
            new DuplicateServiceName(),
            new DuplicateServicePrepare(),
            new DuplicateServiceType(),
            new MultiplePrimaryForAbstractService(),
            new NonPublicServiceDelegate(),
            new NonPublicServicePrepare()
        ];

        $this->validator = new LogicalConstraintValidator(...$this->logicalConstraints);
    }

    public function name() : string {
        return 'validate';
    }

    public function help() : string {
        return <<<TEXT
NAME

    validate - Ensure container definition validates against all logical constraints.
    
SYNOPSIS

    <bold>validate</bold> [OPTION]...

DESCRIPTION

    <bold>validate</bold> will analyze your codebase, run a series of Logical Constraint 
    checks, and output any violations found.
    
    Violations are split into three different types:
    
    - Critical
        These errors are highly indicative of a problem that will result in an exception 
        at runtime. It is HIGHLY recommended that these violations are fixed immediately.
        
    - Warning
        These errors are likely indicative of a problem that will result in an exception 
        or error at runtime, but may not based on various conditions. It is recommended 
        that these violations are fixed as soon as possible.
        
    - Notice
        These errors will not cause an exception or error at runtime, but are likely 
        indicative of some problem or misunderstanding in your dependency injection 
        configuration. You should try to fix these violations when possible.

OPTIONS

    --config-file="file-path.xml"

        Set the name of the configuration file to be used. If not provided the
        default value will be "annotated-container.xml".
        
    --list-constraints
    
        Show which logical constraints will be used to validate your container 
        definition. Passing this options will only list constraints, validation 
        will NOT run with this option passed. 
        
    --profile
    
        Set the active profiles that are used when validating the Container. This 
        option can be provided multiple times to set more than 1 profile.

TEXT;
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        if ($input->option('list-constraints') === true) {
            $this->listConstraints($output);
            return 0;
        }
        $configOption = $input->option('config-file')  ?? 'annotated-container.xml';
        $configFile = $this->directoryResolver->configurationPath($configOption);
        if (!is_file($configFile)) {
            throw ConfigurationNotFound::fromMissingFile($configFile);
        }

        $emitter = new Emitter();

        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->noOpContainerFactory(),
            $emitter,
            $this->directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
            new Stopwatch()
        );

        $containerDefinition = null;

        $infoCapturingListener = $this->infoCapturingListener(static function(ContainerDefinition $definition) use(&$containerDefinition) {
            $containerDefinition = $definition;
        });

        $emitter->addListener($infoCapturingListener);

        $inputProfiles = $input->option('profile') ?? ['default'];
        if (is_string($inputProfiles)) {
            $inputProfiles = [$inputProfiles];
        }

        $profiles = Profiles::fromList($inputProfiles);

        $bootstrap->bootstrapContainer(
            profiles: $profiles,
            configurationFile: $configOption
        );
        assert($containerDefinition instanceof ContainerDefinition);

        $results = $this->validator->validate($containerDefinition, $profiles);

        $output->stdout->write('Annotated Container Validation');
        $output->stdout->br();
        $output->stdout->write('Configuration file: ' . $configFile);
        $output->stdout->write('Active Profiles: ' . implode(', ', $profiles->toArray()));
        $output->stdout->br();
        $output->stdout->write('To view validations ran, execute "annotated-container validate --list-constraints"');
        $output->stdout->br();

        $banner = str_repeat('*', 80);
        if (count($results) === 0) {
            $output->stdout->write('<fg:green>No logical constraint violations were found!</fg:green>');
        } else {
            foreach ($results as $index => $result) {
                $violationType = $result->violationType->name;
                $violationMessage = trim($result->message);

                $violationColor = match ($result->violationType) {
                    LogicalConstraintViolationType::Critical => 'red',
                    LogicalConstraintViolationType::Warning => 'yellow',
                    default => 'red'
                };

                $index++;
                $output->stdout->write(sprintf('Violation #%1$d - <fg:%2$s>%3$s</fg:%2$s>', $index, $violationColor, $violationType));
                $output->stdout->write($banner);
                $output->stdout->br();
                $output->stdout->write($violationMessage);
                $output->stdout->br();
            }

            $output->stdout->write(sprintf('<bold><fg:red>ERROR!</fg:red></bold> Total violations found: <bold>%d</bold>', count($results)));
        }

        return 0;
    }

    private function noOpContainerFactory() : ContainerFactory {
        return new class implements ContainerFactory {
            public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
                return new class implements AnnotatedContainer {

                    public function backingContainer() : object {
                        throw new \RuntimeException(__METHOD__);
                    }

                    public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                        throw new \RuntimeException(__METHOD__);
                    }

                    public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                        throw new \RuntimeException(__METHOD__);
                    }

                    public function get(string $id) {
                        throw new \RuntimeException(__METHOD__);
                    }

                    public function has(string $id) : bool {
                        throw new \RuntimeException(__METHOD__);
                    }
                };
            }

            public function addParameterStore(ParameterStore $parameterStore) : void {
            }
        };
    }

    /**
     * @param Closure(ContainerDefinition):void $closure
     * @return AfterBootstrap
     */
    private function infoCapturingListener(Closure $closure) : AfterBootstrap {
        return new class($closure) implements AfterBootstrap {
            /**
             * @param Closure(ContainerDefinition):void $closure
             */
            public function __construct(
                private readonly Closure $closure
            ) {
            }

            public function handleAfterBootstrap(BootstrappingConfiguration $bootstrappingConfiguration, ContainerDefinition $containerDefinition, AnnotatedContainer $container, ContainerAnalytics $containerAnalytics) : void {
                ($this->closure)($containerDefinition);
            }
        };
    }

    private function listConstraints(TerminalOutput $output) : void {
        $output->stdout->write('Annotated Container Validation');
        $output->stdout->br();
        $output->stdout->write('The following constraints will be checked when validate is ran:');
        $output->stdout->br();

        foreach ($this->logicalConstraints as $logicalConstraint) {
            $output->stdout->write('- ' . $logicalConstraint::class);
        }
    }
}
