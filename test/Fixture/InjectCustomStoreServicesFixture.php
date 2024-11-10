<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class InjectCustomStoreServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectCustomStoreServices';
    }

    public function scalarInjector() : Type {
        return types()->class(InjectCustomStoreServices\ScalarInjector::class);
    }
}
