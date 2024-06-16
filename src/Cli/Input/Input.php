<?php

namespace Cspray\AnnotatedContainer\Cli\Input;

use Cspray\AnnotatedContainer\Cli\Exception\OptionNotFound;

interface Input {

    /**
     * @return array<non-empty-string, list<string|bool>|string|bool>
     */
    public function options() : array;

    /**
     * @return list<non-empty-string>
     */
    public function arguments() : array;

    /**
     * @return list<string|bool>|string|bool|null
     */
    public function option(string $opt) : array|string|bool|null;

    /**
     * @return list<string|bool>|string|bool
     * @throws OptionNotFound
     */
    public function requireOption(string $opt) : array|string|bool;
}
