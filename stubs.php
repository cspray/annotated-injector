<?php declare(strict_types=1);

namespace Psr\Container {

    interface ContainerInterface {

        /**
         * @psalm-template T of object
         * @psalm-param class-string<T>|non-empty-string $id
         * @psalm-return ($id is class-string<T> ? T : mixed)
         */
        public function get(string $id);

    }


}

namespace Auryn {

    class Injector {

        /**
         * @psalm-return array{
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

namespace Illuminate\Contracts\Container {

    interface Container {

        /**
         * @psalm-template T of object
         * @psalm-param non-empty-string|class-string $abstract
         * @psalm-return ($abstract is class-string<T> ? T : mixed)
         */
        public function make(string $abstract, array $parameters = []);


    }

}