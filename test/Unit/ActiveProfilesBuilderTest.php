<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Profiles\ActiveProfilesBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ActiveProfilesBuilderTest extends TestCase {

    public function testAddNoProfilesThrowsException() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('When adding a profile at least 1 value must be provided.');

        ActiveProfilesBuilder::hasDefault()->add()->build();
    }

    public function testAddDefaultProfileExplicitlyThrowsException() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The \'default\' profile is already active and should not be added explicitly.');

        ActiveProfilesBuilder::hasDefault()->add('default');
    }

    public function testAddSingleDuplicateProfileThrowsException() : void {
        $builder = ActiveProfilesBuilder::hasDefault()->add('foo', 'bar', 'baz');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'baz' profile is already active and cannot be added again.");
        $builder->add('qux', 'baz');
    }

    public function testAddMultipleDuplicateProfileThrowsException() {
        $builder = ActiveProfilesBuilder::hasDefault()->add('foo', 'bar', 'baz');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'foo', 'baz' profiles are already active and cannot be added again.");
        $builder->add('qux', 'baz', 'foo');
    }

    public function testAddIfDefaultProfileExplicitlyThrowsException() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The \'default\' profile is already active and should not be added explicitly.');

        ActiveProfilesBuilder::hasDefault()->addIf('default', fn() => true);
    }

    public function testAddIfTrueDuplicateProfileThrowsException() : void {
        $builder = ActiveProfilesBuilder::hasDefault()->add('foo', 'bar', 'baz');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'baz' profile is already active and cannot be added again.");
        $builder->addIf('baz', fn() => true);
    }

    public function testAddAllIfDefaultProfileExplicitlyThrowsException() : void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The \'default\' profile is already active and should not be added explicitly.');
        ActiveProfilesBuilder::hasDefault()->addAllIf(['default'], fn() => true);
    }

    public function testAddAllIfTrueSingleDuplicateProfileThrowsException() : void {
        $builder = ActiveProfilesBuilder::hasDefault()->add('foo', 'bar', 'baz');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'baz' profile is already active and cannot be added again.");
        $builder->addAllIf(['baz', 'qux'], fn() => true);
    }

    public function testAddAllIfTrueMultipleDuplicateProfileThrowsException() : void {
        $builder = ActiveProfilesBuilder::hasDefault()->add('foo', 'qux', 'bar');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'foo', 'bar' profiles are already active and cannot be added again.");
        $builder->addAllIf(['foo', 'baz', 'bar'], fn() => true);
    }

    public function testAddReturnsDifferentInstance() : void {
        $builder = ActiveProfilesBuilder::hasDefault();
        $addBuilder = $builder->add('foo');

        $this->assertNotSame($builder, $addBuilder);
    }

    public function testAddIfReturnsDifferentInstance() : void {
        $builder = ActiveProfilesBuilder::hasDefault();
        $addIfBuilder = $builder->addIf('profile', fn() => true);

        $this->assertNotSame($builder, $addIfBuilder);
    }

    public function testAddAllIfReturnsDifferentInstance() : void {
        $builder = ActiveProfilesBuilder::hasDefault();
        $addAllIfBuilder = $builder->addAllIf(['foo', 'bar'], fn() => false);

        $this->assertNotSame($builder, $addAllIfBuilder);
    }

    public function testBuildHasDefault() : void {
        $actual = ActiveProfilesBuilder::hasDefault()->build();
        $expected = ['default'];

        $this->assertSame($expected, $actual);
    }

    public function addProvider() : array {
        return [
            [['foo']],
            [['foo', 'bar']],
            [['foo', 'bar', 'baz']]
        ];
    }

    /**
     * @dataProvider addProvider
     */
    public function testAddWithProfilesBuild(array $profiles) : void {
        $actual = ActiveProfilesBuilder::hasDefault()->add(...$profiles)->build();
        $expected = ['default', ...$profiles];

        $this->assertSame($expected, $actual);
    }

    public function testAddIfTrueInList() : void {
        $actual = ActiveProfilesBuilder::hasDefault()
            ->addIf('foo', fn() => true)
            ->build();
        $expected = ['default', 'foo'];
        $this->assertSame($expected, $actual);
    }

    public function testAddIfFalseNotInList() : void {
        $actual = ActiveProfilesBuilder::hasDefault()
            ->addIf('foo', fn() => false)
            ->build();
        $expected = ['default'];
        $this->assertSame($expected, $actual);
    }

    public function testAddIfFalseDuplicateProfileDoesNotThrowException() {
        $actual = ActiveProfilesBuilder::hasDefault()
            ->add('foo', 'baz')
            ->addIf('baz', fn() => false)
            ->build();
        $expected = ['default', 'foo', 'baz'];
        $this->assertSame($expected, $actual);
    }

    public function testAddAllIfTrueInList() : void {
        $actual = ActiveProfilesBuilder::hasDefault()
            ->addAllIf(['foo', 'bar'], fn() => true)
            ->build();
        $expected = ['default', 'foo', 'bar'];
        $this->assertSame($expected, $actual);
    }

    public function testAddAllIfFalseNotInList() : void {
        $actual = ActiveProfilesBuilder::hasDefault()
            ->addAllIf(['foo', 'bar'], fn() => false)
            ->build();
        $expected = ['default'];
        $this->assertSame($expected, $actual);
    }

    public function testAddAllIfFalseDuplicateProfileDoesNotThrowException() : void {
        $actual = ActiveProfilesBuilder::hasDefault()
            ->add('foo')
            ->addAllIf(['foo', 'bar'], fn() => false)
            ->build();
        $expected = ['default', 'foo'];

        $this->assertSame($expected, $actual);
    }
}
