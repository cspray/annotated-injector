<?php

namespace Cspray\AnnotatedContainer\Exception;

final class ParameterStoreNotFound extends Exception {

    public static function fromParameterStoreNotAddedToContainerFactory(string $store) : self {
        $message = sprintf(
            'The ParameterStore "%s" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.',
            $store
        );
        return new self($message);
    }
}
