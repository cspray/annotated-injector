<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\AliasResolution;

enum AliasResolutionReason {
    case NoConcreteService;
    case SingleConcreteService;
    case MultipleConcreteService;
    case ConcreteServiceIsPrimary;
    case ConcreteServiceHasPrioritizedProfile;
    case ServiceIsDelegated;
    case MultiplePrimaryService;
}
