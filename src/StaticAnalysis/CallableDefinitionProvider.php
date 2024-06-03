<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

/**
 * A DefinitionProvider that allows you to pass in an anonymous function or some other callable without having to
 * implement your own type.
 */
final class CallableDefinitionProvider implements DefinitionProvider {

    private $callable;

    public function __construct(callable $callable) {
        $this->callable = $callable;
    }

    /**
     * @param DefinitionProviderContext $context
     * @return void
     */
    public function consume(DefinitionProviderContext $context) : void {
        ($this->callable)($context);
    }
}
