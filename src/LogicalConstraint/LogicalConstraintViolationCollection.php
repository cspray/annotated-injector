<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\LogicalConstraint;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Returns a collection where elements are guaranteed to be instances of LogicalConstraintViolation.
 *
 * @implements IteratorAggregate<int, LogicalConstraintViolation>
 */
final class LogicalConstraintViolationCollection implements Countable, IteratorAggregate {

    /**
     * @var list<LogicalConstraintViolation>
     */
    private array $constraintViolations = [];

    /**
     * Will add any violations in $constraintViolationCollection to this collection.
     *
     * This is a mutable operation.
     *
     * @param LogicalConstraintViolationCollection $constraintViolationCollection
     * @return void
     */
    public function addAll(LogicalConstraintViolationCollection $constraintViolationCollection) : void {
        foreach ($constraintViolationCollection as $constraintViolation) {
            $this->constraintViolations[] = $constraintViolation;
        }
    }

    public function add(LogicalConstraintViolation $logicalConstraintViolation) : void {
        $this->constraintViolations[] = $logicalConstraintViolation;
    }

    public function get(int $index) : ?LogicalConstraintViolation {
        return $this->constraintViolations[$index] ?? null;
    }

    /**
     * @return Traversable<int, LogicalConstraintViolation>
     */
    public function getIterator() : Traversable {
        return new ArrayIterator($this->constraintViolations);
    }

    public function count() : int {
        return count($this->constraintViolations);
    }
}
