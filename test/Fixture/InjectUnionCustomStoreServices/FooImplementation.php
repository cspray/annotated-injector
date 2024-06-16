<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices;

// We are intentionally not annotating this as a Service because we want the custom inject store to be what
// determines the exact implementation to use
class FooImplementation implements FooInterface {

}
