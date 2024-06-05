<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Autowire;

use Cspray\AnnotatedContainer\Exception\InvalidAutowireParameter;
use Cspray\AnnotatedContainer\Exception\AutowireParameterNotFound;
use Countable;
use IteratorAggregate;

/**
 * A set of AutowireableParameters that can be used with the AutowireableFactory and AutowireableInvoker.
 *
 * @see autowiredParams()
 * @template-extends IteratorAggregate<int, AutowireableParameter>
 */
interface AutowireableParameterSet extends Countable, IteratorAggregate {

    /**
     * @param AutowireableParameter $autowireableParameter The parameter that should be added to the set
     * @return void
     * @throws InvalidAutowireParameter Thrown if an error was found with the passed parameter
     */
    public function add(AutowireableParameter $autowireableParameter) : void;

    /**
     * @param int $index The 0-based index for which parameter to retrieve
     * @return AutowireableParameter The parameter at the given index.
     * @throws AutowireParameterNotFound Thrown if an $index is passed that has no corresponding parameter
     */
    public function get(int $index) : AutowireableParameter;

    /**
     * @param int $index The 0-based index for which parameter to check the existence of
     * @return bool Whether a parameter can be found at the given index.
     */
    public function has(int $index) : bool;
}
