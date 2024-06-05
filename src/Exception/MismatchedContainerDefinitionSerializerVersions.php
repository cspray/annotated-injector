<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;

final class MismatchedContainerDefinitionSerializerVersions extends Exception {

    public static function fromVersionIsNotInstalledAnnotatedContainerVersion(
        string $version
    ) : self {
        return new self(sprintf(
            'The cached ContainerDefinition is from a version of Annotated Container, "%s", that is not the ' .
            'currently installed version, "%s". Whenever Annotated Container is upgraded this cache must be ',
            $version,
            AnnotatedContainerVersion::version()
        ));
    }
}
