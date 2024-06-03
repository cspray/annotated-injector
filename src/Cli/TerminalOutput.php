<?php

namespace Cspray\AnnotatedContainer\Cli;

use Stringable;

final class TerminalOutput {

    public readonly OutputWithHelpers $stdout;
    public readonly OutputWithHelpers $stderr;

    public function __construct(Output $stdout = null, Output $stderr = null) {
        $this->stdout = $this->getDecoratedOutput($stdout ?? new Stdout());
        $this->stderr = $this->getDecoratedOutput($stderr ?? new Stderr());
    }

    private function getDecoratedOutput(Output $output) : OutputWithHelpers {
        return new class($output) implements Output, OutputWithHelpers {

            /**
             * @var array<string, array{open: numeric-string, close: numeric-string}>
             */
            private const FORMATS = [
                'bold' => ['open' => '1', 'close' => '22'],
                'em' => ['open' => '3', 'close' => '23'],
                'underline' => ['open' => '4', 'close' => '24'],
                'dim' => ['open' => '2', 'close' => '22'],
                'del' => ['open' => '9', 'close' => '29'],

                'fg:black' => ['open' => '30', 'close' => '0'],
                'bg:black' => ['open' => '40', 'close' => '0'],
                'fg:red' => ['open' => '31', 'close' => '0'],
                'bg:red' => ['open' => '41', 'close' => '0'],
                'fg:green' => ['open' => '32', 'close' => '0'],
                'bg:green' => ['open' => '42', 'close' => '0'],
                'fg:yellow' => ['open' => '33', 'close' => '0'],
                'bg:yellow' => ['open' => '43', 'close' => '0'],
                'fg:blue' => ['open' => '34', 'close' => '0'],
                'bg:blue' => ['open' => '44', 'close' => '0'],
                'fg:magenta' => ['open' => '35', 'close' => '0'],
                'bg:magenta' => ['open' => '45', 'close' => '0'],
                'fg:cyan' => ['open' => '36', 'close' => '0'],
                'bg:cyan' => ['open' => '46', 'close' => '0'],
                'fg:white' => ['open' => '37', 'close' => '0'],
                'bg:white' => ['open' => '47', 'close' => '0']
            ];

            private readonly array $tagCodes;

            public function __construct(private readonly Output $output) {
                $tags = [];
                $codes = [];
                foreach (self::FORMATS as $tag => $code) {
                    $openTag = sprintf('<%s>', $tag);
                    $closeTag = sprintf('</%s>', $tag);
                    $openCode = sprintf("\033[%sm", $code['open']);
                    $exitCode = sprintf("\033[%sm", $code['close']);

                    $tags[] = $openTag;
                    $tags[] = $closeTag;
                    $codes[] = $openCode;
                    $codes[] = $exitCode;
                }
                $this->tagCodes = [
                    'tags' => $tags,
                    'codes' => $codes
                ];
            }

            public function write(string|Stringable $msg, bool $appendNewLine = true) : void {
                $msg = str_replace($this->tagCodes['tags'], $this->tagCodes['codes'], (string) $msg);
                $this->output->write($msg, $appendNewLine);
            }

            public function br() : void {
                $this->output->write(PHP_EOL, false);
            }
        };
    }
}
