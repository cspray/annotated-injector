<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;

/**
 * @Internal
 */
enum AttributeType : string {
    case Inject = InjectAttribute::class;
    case Service = ServiceAttribute::class;
    case ServiceDelegate = ServiceDelegateAttribute::class;
    case ServicePrepare = ServicePrepareAttribute::class;
}
