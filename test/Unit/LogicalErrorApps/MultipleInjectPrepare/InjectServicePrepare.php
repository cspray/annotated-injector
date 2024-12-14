<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\MultipleInjectPrepare;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class InjectServicePrepare {

    #[ServicePrepare]
    public function setValue(
        #[Inject('foo')]
        #[Inject('bar')]
        string $value
    ) : void {
    }
}
