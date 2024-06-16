<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;

final class PrivateServicePrepareFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/PrivateServicePrepareMethod';
    }
}
