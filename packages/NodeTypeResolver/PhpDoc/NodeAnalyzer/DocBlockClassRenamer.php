<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser\RenamingPhpDocNodeVisitorFactory;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;

final class DocBlockClassRenamer
{
    /**
     * @var ClassRenamePhpDocNodeVisitor
     */
    private $classRenamePhpDocNodeVisitor;

    /**
     * @var RenamingPhpDocNodeVisitorFactory
     */
    private $renamingPhpDocNodeVisitorFactory;

    public function __construct(
        ClassRenamePhpDocNodeVisitor $classRenamePhpDocNodeVisitor,
        RenamingPhpDocNodeVisitorFactory $renamingPhpDocNodeVisitorFactory
    ) {
        $this->classRenamePhpDocNodeVisitor = $classRenamePhpDocNodeVisitor;
        $this->renamingPhpDocNodeVisitorFactory = $renamingPhpDocNodeVisitorFactory;
    }

    /**
     * @param OldToNewType[] $oldToNewTypes
     */
    public function renamePhpDocType(PhpDocInfo $phpDocInfo, array $oldToNewTypes): void
    {
        if ($oldToNewTypes === []) {
            return;
        }

        $phpDocNodeTraverser = $this->renamingPhpDocNodeVisitorFactory->create();
        $this->classRenamePhpDocNodeVisitor->setOldToNewTypes($oldToNewTypes);

        $phpDocNodeTraverser->traverse($phpDocInfo->getPhpDocNode());
    }
}
