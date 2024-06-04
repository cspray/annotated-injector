<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Event\Listener;

final class InvalidListener extends Exception {

    public static function fromListenerNotKnownType(Listener $listener) : self {
        return new self(sprintf(
            '%s implements %s but is not a known types provided by Annotated Container. You should not implement' .
            ' the %s interface directly, instead choosing to use a specific interface under the ' .
            'Cspray\\AnnotatedContainer\\Event\\Listener namespace.',
            $listener::class,
            Listener::class,
            Listener::class
        ));
    }
}
