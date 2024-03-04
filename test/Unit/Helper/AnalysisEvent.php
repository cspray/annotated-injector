<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

enum AnalysisEvent {
    case AnalyzedContainerDefinitionFromCache;
    case AnalyzedInjectDefinitionFromAttribute;
    case AnalyzedServiceDefinitionFromAttribute;
    case AnalyzedServiceDelegateDefinitionFromAttribute;
    case AnalyzedServicePrepareDefinitionFromAttribute;
    case AddedAliasDefinition;
    case AddedInjectDefinitionFromApi;
    case AddedServiceDefinitionFromApi;
    case AddedServiceDelegateDefinitionFromApi;
    case AddedServicePrepareDefinitionFromApi;

    case BeforeContainerAnalysis;
    case AfterContainerAnalysis;

}
