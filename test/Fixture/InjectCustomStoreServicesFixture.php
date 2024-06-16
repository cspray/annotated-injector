<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class InjectCustomStoreServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectCustomStoreServices';
    }

    public function scalarInjector() : ObjectType {
        return objectType(InjectCustomStoreServices\ScalarInjector::class);
    }
}
