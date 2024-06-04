<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Event\Event;
use Cspray\AnnotatedContainer\Event\EventName;
use Cspray\AnnotatedContainer\Event\Listener;

final class AlwaysHandlingEventCapturingListener implements Listener {

    private array $events = [];

    public function canHandle(EventName $eventName) : bool {
        return true;
    }

    public function handle(Event $event) : void {
        $this->events[] = $event;
    }

    /**
     * @return list<Event>
     */
    public function getCapturedEvents() : array {
        return $this->events;
    }
}
