<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\Profiles;
use PHPUnit\Framework\TestCase;

final class ContainerFactoryOptionsBuilderTest extends TestCase {

    public function testGetProfiles() : void {
        $options = ContainerFactoryOptionsBuilder::forProfiles(Profiles::fromList(['default', 'dev', 'local']))
            ->build();

        self::assertSame(['default', 'dev', 'local'], $options->getProfiles()->toArray());
    }
}
