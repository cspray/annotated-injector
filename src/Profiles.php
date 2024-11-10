<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\Exception\InvalidProfiles;

final class Profiles {

    /**
     * @var non-empty-list<non-empty-string> $profiles
     */
    private readonly array $profiles;

    /**
     * @param list<string> $profiles
     */
    private function __construct(array $profiles) {
        if ($profiles === []) {
            throw InvalidProfiles::fromEmptyProfilesList();
        }

        $clean = [];
        foreach ($profiles as $profile) {
            if ($profile === '') {
                throw InvalidProfiles::fromEmptyProfile();
            }
            $clean[] = $profile;
        }

        $this->profiles = $clean;
    }

    public static function defaultOnly() : self {
        return new self(['default']);
    }

    /**
     * @param list<string> $profiles
     * @return self
     * @throws InvalidProfiles
     */
    public static function fromList(array $profiles) : self {
        return new self($profiles);
    }

    public static function fromCommaDelimitedString(string $profiles) : self {
        return self::fromDelimitedString($profiles, ',');
    }

    /**
     * @param string $profilesString
     * @param non-empty-string $delimiter
     * @return self
     * @throws InvalidProfiles
     */
    public static function fromDelimitedString(string $profilesString, string $delimiter) : self {
        return new self(array_map(
            static fn(string $profile) => trim($profile),
            explode($delimiter, $profilesString)
        ));
    }

    /**
     * @param non-empty-string $profile
     * @return bool
     */
    public function isActive(string $profile) : bool {
        return in_array($profile, $this->profiles, true);
    }

    /**
     * @param list<non-empty-string> $profiles
     * @return bool
     */
    public function isAnyActive(array $profiles) : bool {
        return count(array_intersect($this->profiles, $profiles)) >= 1;
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function toArray() : array {
        return $this->profiles;
    }
}
