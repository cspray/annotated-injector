<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ServicePrepareNotService;

use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

class FooImplementation {

    #[ServicePrepare]
    public function postConstruct() {
    }
}
