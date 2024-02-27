<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Exception\InvalidProfiles;
use Cspray\AnnotatedContainer\Profiles;
use PHPUnit\Framework\TestCase;

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

    public function testPassEmptyListToProfilesFromListThrowsException() : void {
        $this->expectException(InvalidProfiles::class);
        $this->expectExceptionMessage('A non-empty list of non-empty strings MUST be provided for Profiles.');

        Profiles::fromList([]);
    }

    public function testPassEmptyProfileToProfilesFromListThrowsException() : void {
        $this->expectException(InvalidProfiles::class);
        $this->expectExceptionMessage('All profiles MUST be non-empty strings.');

        Profiles::fromList(['']);
    }
}
