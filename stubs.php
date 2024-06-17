<?php declare(strict_types=1);

namespace Auryn {

    class Injector {

        /**
         * @return array{
         *     classDefinitions: list<mixed>,
         *     delegates: list<mixed>,
         *     prepares: list<mixed>,
         *     aliases: list<mixed>,
         *     shares: list<mixed>
         * }
         */
        public function inspect(string $nameFilter = null, string $typeFilter = null) : array {}

        /**
         * @psalm-template T of object
         * @psalm-param non-empty-string|class-string $name
         * @psalm-return ($name is class-string<T> ? T : mixed)
         */
        public function make(string $name, array $args = []) : mixed {}

    }

}