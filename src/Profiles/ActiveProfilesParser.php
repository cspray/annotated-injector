<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Profiles;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateActiveProfilesInFavorOfConcreteValueObject;
use JetBrains\PhpStorm\Deprecated;

/**
 * An implementation that can be used to parse a string into an array of active profiles.
 *
 * It is recommended to use this type of implementation over ActiveProfilesBuilder when your list of active profiles is
 * stored on the running environment. For example, in an environment variable or some other hard-coded string.
 */
#[
    DeprecateActiveProfilesInFavorOfConcreteValueObject,
    Deprecated('Please see DeprecateActiveProfilesInFavorOfConcreteValueObject ADR')
]
interface ActiveProfilesParser {

    /**
     * Based on a described format turn the $profiles string into an array of string values representing the active
     * profiles.
     *
     * @param string $profiles
     * @return string[]
     */
    public function parse(string $profiles) : array;

}