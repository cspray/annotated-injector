<?php

namespace Cspray\AnnotatedContainer\Cli;

use Cspray\AnnotatedContainer\Cli\Exception\OptionNotFound;

final class InputParser {

    /**
     * @param list<string> $argv
     * @return Input
     */
    public function parse(array $argv) : Input {
        array_shift($argv);
        $options = [];
        /** @var list<non-empty-string> $arguments */
        $arguments = [];

        $handleOption = function(string $arg) use(&$options) : void {
            assert(is_array($options));
            if (str_contains($arg, '=')) {
                [$opt, $val] = explode('=', $arg);
            } else {
                $opt = $arg;
                $val = true;
            }
            $opt = str_replace('--', '', $opt);

            if (isset($options[$opt])) {
                $optVal = $val;
                if (!is_array($options[$opt])) {
                    $val = [$options[$opt]];
                } else {
                    $val = $options[$opt];
                }
                $val[] = $optVal;
            }
            $options[$opt] = $val;
        };

        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $handleOption($arg);
            } elseif (str_starts_with($arg, '-')) {
                if (str_contains($arg, '=')) {
                    $handleOption('-' . $arg);
                } else {
                    $arg = str_replace('-', '', $arg);
                    $shortOpts = str_split($arg);
                    foreach ($shortOpts as $shortOpt) {
                        $handleOption('--' . $shortOpt);
                    }
                }
            } else {
                $arguments[] = $arg;
            }
        }

        return new class($options, $arguments) implements Input {

            /**
             * @param array<non-empty-string, list<string>|string|bool> $options
             * @param list<non-empty-string> $args
             */
            public function __construct(
                private readonly array $options,
                private readonly array $args
            ) {
            }

            /**
             * @return array<non-empty-string, list<string>|string|bool>
             */
            public function options() : array {
                return $this->options;
            }

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
