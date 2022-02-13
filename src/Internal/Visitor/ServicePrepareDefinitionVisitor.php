<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Internal\Visitor;

use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\ServicePrepareDefinition;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\ClassMethod;

final class ServicePrepareDefinitionVisitor extends AbstractNodeVisitor implements NodeVisitor {

    private array $servicePrepareDefinitions = [];

    public function enterNode(Node $node) {
        if ($node instanceof ClassMethod) {
            $servicePrepareAttribute = $this->findAttribute(ServicePrepare::class, ...$node->attrGroups);
            if (isset($servicePrepareAttribute)) {
                $this->servicePrepareDefinitions[] = [
                    'definitionType' => ServicePrepareDefinition::class,
                    'type' => $node->getAttribute('parent')->namespacedName->toString(),
                    'method' => $node->name->toString()
                ];
            }
        }
    }

    public function getServicePrepareDefinitions() : array {
        return $this->servicePrepareDefinitions;
    }

}