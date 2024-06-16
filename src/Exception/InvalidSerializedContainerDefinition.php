<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use LibXMLError;

final class InvalidSerializedContainerDefinition extends Exception {

    /**
     * @param list<LibXMLError> $errors
     * @return self
     */
    public static function fromNotValidateXmlSchema(array $errors) : self {
        $errorMessages = join(
            PHP_EOL,
            array_map(
                static fn (LibXMLError $error) => sprintf('- %s', trim($error->message)),
                $errors
            )
        );
        $text = <<<TEXT
The provided container definition does not validate against the schema.

Errors encountered:

$errorMessages

TEXT;

        return new self($text);
    }
}
