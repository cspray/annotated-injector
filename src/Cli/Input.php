<?php

namespace Cspray\AnnotatedContainer\Cli;

use Cspray\AnnotatedContainer\Cli\Exception\OptionNotFound;

interface Input {

    /**
     * @return array<non-empty-string, list<string>|string|bool>
     */
    public function getOptions() : array;

    /**
     * @return list<non-empty-string>
     */
    public function getArguments() : array;

    /**
     * @return list<string>|string|bool|null
     */
    public function getOption(string $opt) : array|string|bool|null;

    /**
     * @return list<string>|string|bool
     * @throws OptionNotFound
     */
    public function requireOption(string $opt) : array|string|bool;
}
