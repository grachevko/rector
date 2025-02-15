<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * Useful for modification of class outside current node tree
 */
final class FileNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var File
     */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @return Node
     */
    public function enterNode(Node $node): ?Node
    {
        $node->setAttribute(AttributeKey::FILE, $this->file);
        return $node;
    }
}
