<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command\Service;

interface ConfigFileNameDecider {

    /**
     * Return the name of the configuration file, without path, that should be used for Annotated Container.
     *
     * @return non-empty-string|null
     */
    public function configFileName() : ?string;

}
