<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

final class Fixtures {

    private function __construct() {}

    public static function getRootPath() : string {
        return __DIR__;
    }

    public static function singleConcreteService() : SingleConcreteServiceFixture {
        return new SingleConcreteServiceFixture();
    }

    public static function implicitAliasedServices() : ImplicitAliasedServicesFixture {
        return new ImplicitAliasedServicesFixture();
    }

    public static function nonAnnotatedServices() : NonAnnotatedServicesFixture {
        return new NonAnnotatedServicesFixture();
    }

    public static function profileResolvedServices() : ProfileResolvedServicesFixture {
        return new ProfileResolvedServicesFixture();
    }

    public static function interfacePrepareServices() : InterfacePrepareServicesFixture {
        return new InterfacePrepareServicesFixture();
    }

    public static function classOverridesPrepareServices() : ClassOverridesPrepareServicesFixture {
        return new ClassOverridesPrepareServicesFixture();
    }

    public static function classOnlyPrepareServices() : ClassOnlyPrepareServicesFixture {
        return new ClassOnlyPrepareServicesFixture();
    }

    public static function subNamespacedServices() : SubNamespacedServicesFixture {
        return new SubNamespacedServicesFixture();
    }

    public static function abstractClassAliasedService() : AbstractClassAliasedServiceFixture {
        return new AbstractClassAliasedServiceFixture();
    }

    public static function ambiguousAliasedServices() : AmbiguousAliasedServicesFixture {
        return new AmbiguousAliasedServicesFixture();
    }

    public static function delegatedService() : DelegatedServiceFixture {
        return new DelegatedServiceFixture();
    }

    public static function primaryAliasedServices() : PrimaryAliasedServicesFixture {
        return new PrimaryAliasedServicesFixture();
    }

    public static function namedServices() : NamedServicesFixture {
        return new NamedServicesFixture();
    }

    public static function implicitAliasThroughAbstractServices() : ImplicitAliasThroughAbstractClassServicesFixture {
        return new ImplicitAliasThroughAbstractClassServicesFixture();
    }

    public static function thirdPartyServices() : ThirdPartyServicesFixture {
        return new ThirdPartyServicesFixture();
    }

    public static function injectConstructorServices() : InjectConstructorServicesFixture {
        return new InjectConstructorServicesFixture();
    }

    public static function injectServiceConstructorServices() : InjectServiceConstructorServicesFixture {
        return new InjectServiceConstructorServicesFixture();
    }

    public static function injectPrepareServices() : InjectPrepareServicesFixture {
        return new InjectPrepareServicesFixture();
    }

    public static function injectCustomStoreServices() : InjectCustomStoreServicesFixture {
        return new InjectCustomStoreServicesFixture();
    }

    public static function multiplePrepareServices() : MultiplePrepareServicesFixture {
        return new MultiplePrepareServicesFixture();
    }

    public static function configurationServices() : ConfigurationServicesFixture {
        return new ConfigurationServicesFixture();
    }

    public static function namedConfigurationServices() : NamedConfigurationServicesFixture {
        return new NamedConfigurationServicesFixture();
    }

    public static function autowireableFactoryServices() : AutowireableFactoryServicesFixture {
        return new AutowireableFactoryServicesFixture();
    }

    public static function injectNamedServices() : InjectNamedServicesFixture {
        return new InjectNamedServicesFixture();
    }

    public static function multiPropConfigurationServices() : MultiPropConfigurationServicesFixture {
        return new MultiPropConfigurationServicesFixture();
    }

    public static function injectServiceIntersectConstructorServices() : InjectServiceIntersectConstructorServicesFixture {
        return new InjectServiceIntersectConstructorServicesFixture();
    }

    public static function injectUnionCustomStoreServices() : InjectUnionCustomStoreServicesFixture {
        return new InjectUnionCustomStoreServicesFixture();
    }

    public static function injectIntersectCustomStoreServices() : InjectIntersectCustomStoreServicesFixture {
        return new InjectIntersectCustomStoreServicesFixture();
    }

    public static function namedProfileResolvedServices() : NamedProfileResolvedServicesFixture {
        return new NamedProfileResolvedServicesFixture();
    }

    public static function configurationMissingStore() : ConfigurationMissingStoreFixture {
        return new ConfigurationMissingStoreFixture();
    }

    public static function implicitServiceDelegateType() : ImplicitServiceDelegateTypeFixture {
        return new ImplicitServiceDelegateTypeFixture();
    }

    public static function implicitServiceDelegateUnionType() : ImplicitServiceDelegateUnionTypeFixture {
        return new ImplicitServiceDelegateUnionTypeFixture();
    }

    public static function configurationWithEnum() : ConfigurationWithEnumFixture {
        return new ConfigurationWithEnumFixture();
    }

    public static function configurationWithArrayEnum() : ConfigurationWithArrayEnumFixture {
        return new ConfigurationWithArrayEnumFixture();
    }

    public static function configurationWithAssocArrayEnum() : ConfigurationWithAssocArrayEnumFixture {
        return new ConfigurationWithAssocArrayEnumFixture();
    }

    public static function injectEnumConstructorServices() : InjectEnumConstructorServicesFixture {
        return new InjectEnumConstructorServicesFixture();
    }

    public static function configurationInjectServiceFixture() : ConfigurationInjectContainerServiceFixture {
        return new ConfigurationInjectContainerServiceFixture();
    }

    public static function thirdPartyDelegatedServices() : ThirdPartyDelegatedServicesFixture {
        return new ThirdPartyDelegatedServicesFixture();
    }

    public static function customServiceAttribute() : CustomServiceAttributeFixture {
        return new CustomServiceAttributeFixture();
    }

    public static function injectListOfScalarsFixture() : InjectListOfScalarsFixture {
        return new InjectListOfScalarsFixture();
    }

    public static function constructorPromotedConfigurationFixture() : ConstructorPromotedConfigurationFixture {
        return new ConstructorPromotedConfigurationFixture();
    }

    public static function aliasedConfigurationFixture() : AliasedConfigurationFixture {
        return new AliasedConfigurationFixture();
    }

    public static function duplicateNamedServiceDifferentProfiles() : DuplicateNamedServiceDifferentProfilesFixture {
        return new DuplicateNamedServiceDifferentProfilesFixture();
    }

    public static function beanLikeConfigConcrete() : BeanLikeConfigConcreteFixture {
        return new BeanLikeConfigConcreteFixture();
    }

    public static function beanLikeConfigInterface() : BeanLikeConfigInterfaceFixture {
        return new BeanLikeConfigInterfaceFixture();
    }

    public static function beanLikeConfigAbstract() : BeanLikeConfigAbstractFixture {
        return new BeanLikeConfigAbstractFixture();
    }

}