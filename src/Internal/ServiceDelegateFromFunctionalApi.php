<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;

/**
 * @internal
 */
final class ServiceDelegateFromFunctionalApi implements ServiceDelegateAttribute {

    public function service() : ?string {
        return null;
    }
}
