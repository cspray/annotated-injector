<?php

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidScanDirectories extends Exception {

    public static function fromEmptyList() : self {
        $msg = 'ContainerDefinitionAnalysisOptions must include at least 1 directory to scan, but none were provided.';
        return new self($msg);
    }

    public static function fromDuplicatedDirectories() : self {
        $message = 'ContainerDefinitionAnalysisOptions includes duplicate scan directories. Please pass a distinct set of directories to scan.';
        return new self($message);
    }
}
