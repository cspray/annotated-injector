<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

enum AnnotatedContainerLifecycle {
    case BeforeCompile;
    case AfterCompile;
    case BeforeContainerCreation;
    case AfterContainerCreation;
}