<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\PrioritizedProfileInjectPrepare;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
final class Injector {

    public string $value = '';

    #[ServicePrepare]
    public function setValue(
        #[Inject('foo', profiles: ['foo'])]
        #[Inject('bar', profiles: ['bar'])]
        #[Inject('baz-qux', profiles: ['baz', 'qux'])]
        #[Inject('default')]
        string $value
    ) : void {
        $this->value = $value;
    }
}
