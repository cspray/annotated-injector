<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Input\Input;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;

final class DisabledCommand implements Command {

    public function __construct(

    ) {}


    public function name() : string {
        // TODO: Implement name() method.
    }

    public function summary() : string {
        // TODO: Implement summary() method.
    }

    public function help() : string {
        // TODO: Implement help() method.
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        // TODO: Implement handle() method.
    }
}