<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, AnalysisEvent>
 */
final class AnalysisEventCollection implements Countable, IteratorAggregate {

    /**
     * @param list<AnalysisEvent> $collection
     */
    public function __construct(
        private array $collection = []
    ) {
    }

    public function add(AnalysisEvent $event) : void {
        $this->collection[] = $event;
    }

    public function first() : ?AnalysisEvent {
        return $this->collection[0] ?? null;
    }

    public function last() : ?AnalysisEvent {
        return $this->collection[count($this->collection) - 1] ?? null;
    }

    public function filter(AnalysisEvent $event) : self {
        return new self(array_filter(
            $this->collection,
            static fn (AnalysisEvent $stored) => $stored === $event,
        ));
    }

    /**
     * @return Traversable<int, AnalysisEvent>
     */
    public function getIterator() : Traversable {
        yield from $this->collection;
    }

    public function count() : int {
        return count($this->collection);
    }
}
