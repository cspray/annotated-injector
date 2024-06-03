<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\ConfigurationCannotBeAssignedProfiles;
use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateConfigurationInFavorOfCustomServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ConfigurationAttribute;
use Cspray\Typiphy\ObjectType;
use JetBrains\PhpStorm\Deprecated;

/**
 * Defines a Configuration object.
 *
 * @see ConfigurationDefinitionBuilder
 */
#[
    ConfigurationCannotBeAssignedProfiles,
    DeprecateConfigurationInFavorOfCustomServiceAttribute,
    Deprecated('See ADR record DeprecatedConfigurationInFavorOfCustomServiceAttribute')
]
interface ConfigurationDefinition {

    /**
     * The type of Configuration object.
     *
     * @return ObjectType
     */
    public function getClass() : ObjectType;

    /**
     * An optional "friendly" name used to identify the configuration.
     *
     * @return non-empty-string|null
     */
    public function getName() : ?string;

    public function getAttribute() : ?ConfigurationAttribute;
}
