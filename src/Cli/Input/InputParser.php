<?php

namespace Cspray\AnnotatedContainer\Cli\Input;

use Cspray\AnnotatedContainer\Cli\Exception\BlankArg;
use Cspray\AnnotatedContainer\Cli\Exception\BlankOptName;
use Cspray\AnnotatedContainer\Cli\Exception\OptionNotFound;

final class InputParser {

    /**
     * @param list<string> $argv
     * @return Input
     */
    public function parse(array $argv) : Input {
        array_shift($argv);
        /** @var array<non-empty-string, list<string>|string|bool> $options */
        $options = [];
        /** @var list<non-empty-string> $arguments */
        $arguments = [];

        $handleOption = function(string $arg) use(&$options) : void {
            // Get the initial value defined in the argument list
            if (str_contains($arg, '=')) {
                [$opt, $val] = explode('=', $arg);
            } else {
                $opt = $arg;
                $val = true;
            }

            $opt = str_replace(['--', '-'], '', $opt);
            if ($opt === '') {
                throw BlankOptName::fromBlankOpt();
            }

            // If an option was previously set for the given option
            // And we encounter another one that means we should
            // store this option as an array of values
            if (isset($options[$opt])) {
                // Store the value we originally determined for the option
                // So we can add it to the array later
                $optVal = $val;

                // If the value previously set in option is not an array then
                // we need to convert the value to an array
                if (!is_array($options[$opt])) {
                    $val = [$options[$opt]];
                } else {
                    $val = $options[$opt];
                }

                // Add the value we originally determined for the given option
                $val[] = $optVal;
            }

            $options[$opt] = $val;
        };

        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $handleOption($arg);
            } elseif (str_starts_with($arg, '-')) {
                if (str_contains($arg, '=')) {
                    $handleOption($arg);
                } else {
                    $arg = str_replace('-', '', $arg);
                    $shortOpts = str_split($arg);
                    foreach ($shortOpts as $shortOpt) {
                        $handleOption($shortOpt);
                    }
                }
            } else {
                if ($arg === '') {
                    throw BlankArg::fromBlankArg();
                }
                $arguments[] = $arg;
            }
        }

        return new class($options, $arguments) implements Input {

            /**
             * @param array<non-empty-string, list<string|bool>|string|bool> $options
             * @param list<non-empty-string> $args
             */
            public function __construct(
                private readonly array $options,
                private readonly array $args
            ) {
            }

            /**
             * @return array<non-empty-string, list<string|bool>|string|bool>
             */
            public function options() : array {
                return $this->options;
            }

            /**
             * @return list<non-empty-string>
             */
            public function arguments() : array {
                return $this->args;
            }

            public function option(string $opt) : array|string|bool|null {
                return $this->options[$opt] ?? null;
            }

            public function requireOption(string $opt) : array|string|bool {
                if (!isset($this->options[$opt])) {
                    throw new OptionNotFound(sprintf('The option "%s" was not provided.', $opt));
                }

                return $this->options[$opt];
            }
        };
    }
}
