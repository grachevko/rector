<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\UnderscorePhpDocNodeTraverserFactory;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\UnderscoreRenamePhpDocNodeVisitor;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;

final class PhpDocTypeRenamer
{
    /**
     * @var UnderscoreRenamePhpDocNodeVisitor
     */
    private $underscoreRenamePhpDocNodeVisitor;

    /**
     * @var UnderscorePhpDocNodeTraverserFactory
     */
    private $underscorePhpDocNodeTraverserFactory;

    public function __construct(
        UnderscorePhpDocNodeTraverserFactory $underscorePhpDocNodeTraverserFactory,
        UnderscoreRenamePhpDocNodeVisitor $underscoreRenamePhpDocNodeVisitor
    ) {
        $this->underscoreRenamePhpDocNodeVisitor = $underscoreRenamePhpDocNodeVisitor;
        $this->underscorePhpDocNodeTraverserFactory = $underscorePhpDocNodeTraverserFactory;
    }

    public function changeUnderscoreType(
        PhpDocInfo $phpDocInfo,
        Node $node,
        PseudoNamespaceToNamespace $pseudoNamespaceToNamespace
    ): void {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        $this->underscoreRenamePhpDocNodeVisitor->setPseudoNamespaceToNamespace($pseudoNamespaceToNamespace);
        $this->underscoreRenamePhpDocNodeVisitor->setCurrentPhpParserNode($node);

        $phpDocNodeTraverser = $this->underscorePhpDocNodeTraverserFactory->create();
        $phpDocNodeTraverser->traverse($phpDocNode);
    }
}
