<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

interface ThirdPartyInitializerProvider {

    /**
     * @return list<ThirdPartyInitializer>
     */
    public function thirdPartyInitializers() : array;
}
