<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

interface ThirdPartyInitializerProvider {

    /**
     * @return list<class-string<ThirdPartyInitializer>>
     */
    public function getThirdPartyInitializers() : array;
}
