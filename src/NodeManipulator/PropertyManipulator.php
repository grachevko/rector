<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\Reflection\FunctionLikeReflectionParser;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Guard\VariableToConstantGuard;
use Rector\ReadWrite\NodeAnalyzer\ReadWritePropertyAnalyzer;
use Symplify\PackageBuilder\Php\TypeChecker;

/**
 * "private $property"
 */
final class PropertyManipulator
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    /**
     * @var VariableToConstantGuard
     */
    private $variableToConstantGuard;

    /**
     * @var ReadWritePropertyAnalyzer
     */
    private $readWritePropertyAnalyzer;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var TypeChecker
     */
    private $typeChecker;

    /**
     * @var PropertyFetchFinder
     */
    private $propertyFetchFinder;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var FunctionLikeReflectionParser
     */
    private $functionLikeReflectionParser;

    public function __construct(
        AssignManipulator $assignManipulator,
        BetterNodeFinder $betterNodeFinder,
        VariableToConstantGuard $variableToConstantGuard,
        ReadWritePropertyAnalyzer $readWritePropertyAnalyzer,
        PhpDocInfoFactory $phpDocInfoFactory,
        TypeChecker $typeChecker,
        PropertyFetchFinder $propertyFetchFinder,
        NodeRepository $nodeRepository,
        FunctionLikeReflectionParser $functionLikeReflectionParser
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->assignManipulator = $assignManipulator;
        $this->variableToConstantGuard = $variableToConstantGuard;
        $this->readWritePropertyAnalyzer = $readWritePropertyAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeChecker = $typeChecker;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->nodeRepository = $nodeRepository;
        $this->functionLikeReflectionParser = $functionLikeReflectionParser;
    }

    public function isPropertyUsedInReadContext(Property $property): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($phpDocInfo->hasByAnnotationClasses(['Doctrine\ORM\*', 'JMS\Serializer\Annotation\Type'])) {
            return true;
        }

        $privatePropertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($property);
        foreach ($privatePropertyFetches as $privatePropertyFetch) {
            if ($this->readWritePropertyAnalyzer->isRead($privatePropertyFetch)) {
                return true;
            }
        }

        // has classLike $this->$variable call?
        /** @var ClassLike $classLike */
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);

        return (bool) $this->betterNodeFinder->findFirst($classLike->stmts, function (Node $node): bool {
            if (! $node instanceof PropertyFetch) {
                return false;
            }

            if (! $this->readWritePropertyAnalyzer->isRead($node)) {
                return false;
            }

            return $node->name instanceof Expr;
        });
    }

    public function isPropertyChangeable(Property $property): bool
    {
        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($property);

        foreach ($propertyFetches as $propertyFetch) {
            if ($this->isChangeableContext($propertyFetch)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $expr
     */
    private function isChangeableContext(Expr $expr): bool
    {
        $parent = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            return false;
        }

        if ($this->typeChecker->isInstanceOf($parent, [PreInc::class, PreDec::class, PostInc::class, PostDec::class])) {
            $parent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        }

        if (! $parent instanceof Node) {
            return false;
        }

        if ($parent instanceof Arg) {
            $readArg = $this->variableToConstantGuard->isReadArg($parent);
            if (! $readArg) {
                return true;
            }

            $caller = $parent->getAttribute(AttributeKey::PARENT_NODE);
            if ($caller instanceof MethodCall || $caller instanceof StaticCall) {
                return $this->isFoundByRefParam($caller);
            }
        }

        return $this->assignManipulator->isLeftPartOfAssign($expr);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function isFoundByRefParam(Node $node): bool
    {
        $classMethod = $node instanceof MethodCall
            ? $this->nodeRepository->findClassMethodByMethodCall($node)
            : $this->nodeRepository->findClassMethodByStaticCall($node);

        if (! $classMethod instanceof ClassMethod) {
            $classMethod = $this->functionLikeReflectionParser->parseCaller($node);
        }

        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        $params = $classMethod->getParams();
        foreach ($params as $param) {
            if ($param->byRef) {
                return true;
            }
        }

        return false;
    }
}
