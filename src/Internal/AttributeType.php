<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\ConfigurationAttribute;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;

/**
 * @Internal
 */
enum AttributeType : string {
    case Configuration = ConfigurationAttribute::class;
    case Inject = InjectAttribute::class;
    case Service = ServiceAttribute::class;
    case ServiceDelegate = ServiceDelegateAttribute::class;
    case ServicePrepare = ServicePrepareAttribute::class;
}
