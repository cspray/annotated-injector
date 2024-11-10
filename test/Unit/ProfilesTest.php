<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Exception\InvalidProfiles;
use Cspray\AnnotatedContainer\Profiles;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Closure;

class ProfilesTest extends TestCase {

    public function testProfilesFromListReturnsCorrectArray() : void {
        $subject = Profiles::fromList(['default', 'dev', 'prod']);

        self::assertSame(['default', 'dev', 'prod'], $subject->toArray());
    }

    public function testIsActiveReturnsTrueIfProfileIsListed() : void {
        $profilesList = ['mack', 'nick', 'xoe', 'ada'];
        $subject = Profiles::fromList($profilesList);

        $actual = $profilesList[array_rand($profilesList)];

        self::assertTrue($subject->isActive($actual));
    }

    public function testIsActiveReturnsFalseIfProfileNotListed() : void {
        $subject = Profiles::fromList(['php', 'ruby', 'python']);

        self::assertFalse($subject->isActive('java'));
    }

    public function testIsAnyActiveReturnsTrueIfAnyProfileIsListed() : void {
        $profilesList = ['mack', 'nick', 'xoe', 'ada'];
        $subject = Profiles::fromList($profilesList);

        $actual = [
            'rooster',
            'ginapher',
            $profilesList[array_rand($profilesList)],
        ];

        self::assertTrue($subject->isAnyActive($actual));
    }

    public function testIsAnyActiveReturnsFalseIfNoProfileIsListed() : void {
        $profilesList = ['mack', 'nick', 'xoe', 'ada'];
        $subject = Profiles::fromList($profilesList);

        $actual = [
            'rooster',
            'ginapher',
            'chloe',
        ];

        self::assertFalse($subject->isAnyActive($actual));
    }

    public static function emptyProfilesProvider() : array {
        return [
            'fromList' => [static fn() => Profiles::fromList([])],
        ];
    }

    #[DataProvider('emptyProfilesProvider')]
    public function testPassEmptyListToProfilesFromListThrowsException(Closure $closure) : void {
        $this->expectException(InvalidProfiles::class);
        $this->expectExceptionMessage('A non-empty list of non-empty strings MUST be provided for Profiles.');

        $closure();
    }

    public static function emptyProfileProvider() : array {
        return [
            'fromList' => [static fn() => Profiles::fromList([''])],
            'fromDelimitedString' => [static fn() => Profiles::fromDelimitedString('', ',')],
        ];
    }

    #[DataProvider('emptyProfileProvider')]
    public function testPassEmptyProfileToProfilesFromListThrowsException(Closure $closure) : void {
        $this->expectException(InvalidProfiles::class);
        $this->expectExceptionMessage('All profiles MUST be non-empty strings.');

        $closure();
    }

    public static function delimitedStringProvider() : array {
        return [
            ['foo,bar,baz', ',', ['foo', 'bar', 'baz']],
            ['erykah|badu|on|on', '|', ['erykah', 'badu', 'on', 'on']],
            ['harry/mack/goat', '/', ['harry', 'mack', 'goat']],
            ['  check   ;   for    ;     trailing ; leading   ; spaces', ';', ['check', 'for', 'trailing', 'leading', 'spaces']],
        ];
    }

    #[DataProvider('delimitedStringProvider')]
    public function testDelimitedStringParsedCorrectly(string $profiles, string $delimiter, array $expected) : void {
        $profiles = Profiles::fromDelimitedString($profiles, $delimiter);

        self::assertSame($expected, $profiles->toArray());
    }

    public static function commaDelimitedStringProvider() : array {
        return [
            ['foo,bar,baz', ['foo', 'bar', 'baz']],
            [' not  ,  worth  ,  it  ', ['not', 'worth', 'it']],
            ['some|non|comma|delimiter', ['some|non|comma|delimiter']],
        ];
    }

    #[DataProvider('commaDelimitedStringProvider')]
    public function testCommaDelimitedStringParsedCorrectly(string $profiles, array $expected) : void {
        $profiles = Profiles::fromCommaDelimitedString($profiles);

        self::assertSame($expected, $profiles->toArray());
    }
}
