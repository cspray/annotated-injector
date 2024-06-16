<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class InjectConstructorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectConstructorServices';
    }

    public function injectArrayService() : ObjectType {
        return objectType(InjectConstructorServices\ArrayInjectService::class);
    }

    public function injectIntService() : ObjectType {
        return objectType(InjectConstructorServices\IntInjectService::class);
    }

    public function injectBoolService() : ObjectType {
        return objectType(InjectConstructorServices\BoolInjectService::class);
    }

    public function injectFloatService() : ObjectType {
        return objectType(InjectConstructorServices\FloatInjectService::class);
    }

    public function injectStringService() : ObjectType {
        return objectType(InjectConstructorServices\StringInjectService::class);
    }

    public function injectEnvService() : ObjectType {
        return objectType(InjectConstructorServices\EnvInjectService::class);
    }

    public function injectExplicitMixedService() : ObjectType {
        return objectType(InjectConstructorServices\ExplicitMixedInjectService::class);
    }

    public function injectImplicitMixedService() : ObjectType {
        return objectType(InjectConstructorServices\ImplicitMixedInjectService::class);
    }

    public function injectNullableStringService() : ObjectType {
        return objectType(InjectConstructorServices\NullableStringInjectService::class);
    }

    public function injectProfilesStringService() : ObjectType {
        return objectType(InjectConstructorServices\ProfilesStringInjectService::class);
    }

    public function injectTypeUnionService() : ObjectType {
        return objectType(InjectConstructorServices\TypeUnionInjectService::class);
    }
}
