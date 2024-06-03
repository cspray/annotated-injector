<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\Bootstrap\DelegatedParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore;
use PHPUnit\Framework\TestCase;

final class DelegatedParameterStoreFactoryTest extends TestCase {

    public function testDelegatesToDefaultParameterStoreFactoryIfNoParameterStoreFactoriesPresent() : void {
        $default = $this->getMockBuilder(ParameterStoreFactory::class)->getMock();
        $default->expects($this->once())
            ->method('createParameterStore')
            ->with(StubParameterStore::class)
            ->willReturn(new StubParameterStore());

        $subject = new DelegatedParameterStoreFactory($default);

        self::assertInstanceOf(
            StubParameterStore::class,
            $subject->createParameterStore(StubParameterStore::class)
        );
    }

    public function testDelegatesToAddedParameterStoreFactoryIfPresent() : void {
        $default = $this->getMockBuilder(ParameterStoreFactory::class)->getMock();
        $default->expects($this->never())->method('createParameterStore');

        $delegated = $this->getMockBuilder(ParameterStoreFactory::class)->getMock();
        $delegated->expects($this->once())
            ->method('createParameterStore')
            ->with(StubParameterStore::class)
            ->willReturn(new StubParameterStore());

        $subject = new DelegatedParameterStoreFactory($default);
        $subject->addParameterStoreFactory(StubParameterStore::class, $delegated);

        self::assertInstanceOf(
            StubParameterStore::class,
            $subject->createParameterStore(StubParameterStore::class)
        );
    }
}
