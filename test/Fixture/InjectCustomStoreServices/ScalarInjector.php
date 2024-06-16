<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectCustomStoreServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class ScalarInjector {

    public function __construct(
        #[Inject('key', from: 'test-store')] public readonly string $key
    ) {
    }
}
