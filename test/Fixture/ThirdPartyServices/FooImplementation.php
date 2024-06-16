<?php

namespace Cspray\AnnotatedContainer\Fixture\ThirdPartyServices;

// This is intentionally not annotated with the Service attribute
// We expect the alias for this implementation to be added through the 3rd party functional API

class FooImplementation implements FooInterface {

}
