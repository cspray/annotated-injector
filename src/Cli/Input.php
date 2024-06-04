<?php

namespace Cspray\AnnotatedContainer\Cli;

use Cspray\AnnotatedContainer\Cli\Exception\OptionNotFound;

interface Input {

    /**
     * @return array<non-empty-string, list<string>|string|bool>
     */
    public function options() : array;

    /**
     * @return list<non-empty-string>
     */
    public function arguments() : array;

    /**
     * @return list<string>|string|bool|null
     */
    public function option(string $opt) : array|string|bool|null;

    /**
     * @return list<string>|string|bool
     * @throws OptionNotFound
     */
    public function requireOption(string $opt) : array|string|bool;
}
