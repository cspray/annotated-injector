<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class InjectConstructorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectConstructorServices';
    }

    public function injectArrayService() : Type {
        return types()->class(InjectConstructorServices\ArrayInjectService::class);
    }

    public function injectIntService() : Type {
        return types()->class(InjectConstructorServices\IntInjectService::class);
    }

    public function injectBoolService() : Type {
        return types()->class(InjectConstructorServices\BoolInjectService::class);
    }

    public function injectFloatService() : Type {
        return types()->class(InjectConstructorServices\FloatInjectService::class);
    }

    public function injectStringService() : Type {
        return types()->class(InjectConstructorServices\StringInjectService::class);
    }

    public function injectEnvService() : Type {
        return types()->class(InjectConstructorServices\EnvInjectService::class);
    }

    public function injectExplicitMixedService() : Type {
        return types()->class(InjectConstructorServices\ExplicitMixedInjectService::class);
    }

    public function injectImplicitMixedService() : Type {
        return types()->class(InjectConstructorServices\ImplicitMixedInjectService::class);
    }

    public function injectNullableStringService() : Type {
        return types()->class(InjectConstructorServices\NullableStringInjectService::class);
    }

    public function injectProfilesStringService() : Type {
        return types()->class(InjectConstructorServices\ProfilesStringInjectService::class);
    }

    public function injectTypeUnionService() : Type {
        return types()->class(InjectConstructorServices\TypeUnionInjectService::class);
    }
}
