<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Profiles;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateActiveProfilesInFavorOfConcreteValueObject;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;

#[
    DeprecateActiveProfilesInFavorOfConcreteValueObject,
    Deprecated('Please see DeprecateActiveProfilesInFavorOfConcreteValueObject ADR')
]
final class ActiveProfilesBuilder {

    /**
     * @var list<string>
     */
    private array $profiles = [];

    private function __construct() {
    }

    /**
     * Ensures that the 'default' profile is included.
     *
     * There is not currently a way to use this builder without including the 'default' profile. This is intended behavior,
     * if you do not want 'default' profile to be included you should not use the ActiveProfilesBuilder.
     *
     * @return static
     */
    public static function hasDefault() : self {
        return new self();
    }

    /**
     * Add 1 or more profiles to the list of active profiles.
     *
     * An exception will be thrown if $profiles is empty, contains the 'default' value, or contains a value that has
     * already been added.
     *
     * @param string ...$profiles
     * @return $this
     */
    public function add(string...$profiles) : self {
        if (empty($profiles)) {
            throw new InvalidArgumentException('When adding a profile at least 1 value must be provided.');
        } elseif (in_array('default', $profiles)) {
            throw new InvalidArgumentException("The 'default' profile is already active and should not be added explicitly.");
        } elseif (!empty($dupes = array_intersect($this->profiles, $profiles))) {
            throw new InvalidArgumentException(sprintf(
                "The '%s' %s already active and cannot be added again.",
                join("', '", $dupes),
                count($dupes) === 1 ? 'profile is' : 'profiles are'
            ));
        }
        $instance = clone $this;
        $instance->profiles = [...$this->profiles, ...$profiles];
        return $instance;
    }

    /**
     * Will add a single $profile if the return value from $decider is a truthy value.
     *
     * Will throw an exception if $decider returns true and the $profile is the 'default' value or the $profile has
     * already been added.
     *
     * @param string $profile
     * @param callable $decider
     * @return $this
     */
    public function addIf(string $profile, callable $decider) : self {
        $instance = clone $this;
        if ($decider()) {
            $instance = $instance->add($profile);
        }
        return $instance;
    }

    /**
     * Will add multiple $profiles if the return value from $decider is a truthy value.
     *
     * Will throw an exception if $decider returns true and any value in $profiles is the 'default' value or has already
     * been added.
     *
     * @param list<string> $profiles
     * @param callable $decider
     * @return $this
     */
    public function addAllIf(array $profiles, callable $decider) : self {
        $instance = clone $this;
        if ($decider()) {
            $instance = $instance->add(...$profiles);
        }
        return $instance;
    }

    /**
     * Return an array of active profiles based on previous calls to the builder.
     *
     * @return list<string>
     */
    public function build() : array {
        return ['default', ...$this->profiles];
    }
}
