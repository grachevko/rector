<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class PreviousVariableAssignNodeFinder
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeComparator
     */
    private $nodeComparator;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        NodeComparator $nodeComparator
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
    }

    public function find(Assign $assign): ?Node
    {
        $currentAssign = $assign;

        $variableName = $this->nodeNameResolver->getName($assign->var);
        if ($variableName === null) {
            return null;
        }

        return $this->betterNodeFinder->findFirstPrevious($assign, function (Node $node) use (
            $variableName,
            $currentAssign
        ): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            // skip self
            if ($this->nodeComparator->areSameNode($node, $currentAssign)) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->var, $variableName);
        });
    }
}
