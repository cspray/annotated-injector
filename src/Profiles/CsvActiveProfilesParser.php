<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Profiles;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateActiveProfilesInFavorOfConcreteValueObject;
use JetBrains\PhpStorm\Deprecated;

/**
 * An ActiveProfilesParser that will take a comma-separated string and turn it into a list of active profiles.
 */
#[
    DeprecateActiveProfilesInFavorOfConcreteValueObject,
    Deprecated('Please see DeprecateActiveProfilesInFavorOfConcreteValueObject ADR')
]
final class CsvActiveProfilesParser implements ActiveProfilesParser {

    /**
     * This implementation takes several steps to ensure that you do not receive an empty list and that spaces around
     * profiles are handled properly.
     *
     * For example, the string 'default,dev,local' would result in the list of active profiles being
     * ['default', 'dev', 'local']. The string 'default, dev, local' would result in the same list of active profiles,
     * ['default', 'dev', 'local']. Note that there are not errant spaces, i.e. ' dev' or ' local'. Additionally, if
     * you pass in a string that when split by commas would result in an empty list an error is thrown. For example,
     * ',,' or ', , ' would result in an exception as this would result in an empty list of profiles.
     *
     * @param string $profiles A comma-separated string to turn into a list of strings
     * @throws \InvalidArgumentException If the string is empty or would result in an empty list
     * @return string[]
     */
    public function parse(string $profiles) : array {
        if (empty($profiles)) {
            throw new \InvalidArgumentException('The profiles to parse cannot be an empty string.');
        }
        $parsedProfiles = preg_split('/\s*,\s*/', trim($profiles), flags: PREG_SPLIT_NO_EMPTY);
        if (empty($parsedProfiles)) {
            throw new \InvalidArgumentException(sprintf("The profile string '%s' results in no valid profiles.", $profiles));
        }
        return $parsedProfiles;
    }
}
