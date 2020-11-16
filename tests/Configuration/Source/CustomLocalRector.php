<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration\Source;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\RectorDefinition\RectorDefinition;

final class CustomLocalRector implements RectorInterface
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        // TODO: Implement getDefinition() method.
    }
}
