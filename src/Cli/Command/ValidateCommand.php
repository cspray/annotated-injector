<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\ContainerDefinitionAnalysisOptionsFromBootstrappingConfiguration;
use Cspray\AnnotatedContainer\Cli\Exception\ProfileNotString;
use Cspray\AnnotatedContainer\Cli\Input\Input;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Emitter;
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
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;

final class ValidateCommand implements Command {

    /**
     * @var list<LogicalConstraint>
     */
    private readonly array $logicalConstraints;

    private readonly LogicalConstraintValidator $validator;

    public function __construct(
        private readonly BootstrappingConfiguration $bootstrappingConfiguration,
        private readonly BootstrappingDirectoryResolver $directoryResolver,
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

    public function summary() : string {
        return 'Ensure your ContainerDefinition validates against all logical constraints';
    }

    public function help() : string {
        $summary = $this->summary();
        return <<<TEXT
NAME

    validate - $summary
    
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

        $profiles = $this->profiles($input);

        $analyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new Emitter()
        );
        $containerDefinition = $analyzer->analyze(
            (new ContainerDefinitionAnalysisOptionsFromBootstrappingConfiguration(
                $this->bootstrappingConfiguration,
                $this->directoryResolver
            ))->create()
        );

        assert($containerDefinition instanceof ContainerDefinition);

        $results = $this->validator->validate($containerDefinition, $profiles);

        $output->stdout->write('Annotated Container Validation');
        $output->stdout->br();
        $output->stdout->write('Configuration: ' . $this->bootstrappingConfiguration::class);
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

    private function profiles(Input $input) : Profiles {
        $inputProfiles = $input->option('profiles') ?? [Profiles::DEFAULT_PROFILE];
        if (is_bool($inputProfiles)) {
            throw ProfileNotString::fromNotString();
        }

        if (is_string($inputProfiles)) {
            $inputProfiles = [$inputProfiles];
        }

        $valid = [];
        foreach ($inputProfiles as $profile) {
            if (!is_string($profile)) {
                throw ProfileNotString::fromNotString();
            }
            $valid[] = $profile;
        }

        return Profiles::fromList($valid);
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
