<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Autowire;

/**
 * An object that represents a parameter that should be injected into a method parameter when using the AutowireableFactory
 * or AutowireableInvoker.
 *
 * The primary purpose of this interface is to allow parameters to distinguish between a value that should be injected
 * directly or a value that should be retrieved from the Container as a service.
 *
 * @see serviceParam()
 * @see rawParam()
 */
interface AutowireableParameter {

    /**
     * @return string The name of the parameter the value should be injected into
     */
    public function getName() : string;

    /**
     * @return mixed Whatever value should be injected into a given parameter.
     */
    public function getValue() : mixed;

    /**
     * @return bool Whether the value should be retrieved from the Container or injected directly
     */
    public function isServiceIdentifier() : bool;
}
