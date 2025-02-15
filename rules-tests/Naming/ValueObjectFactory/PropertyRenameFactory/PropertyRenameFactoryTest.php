<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\ValueObjectFactory\PropertyRenameFactory;

use Iterator;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PropertyRenameFactoryTest extends AbstractTestCase
{
    /**
     * @var PropertyRenameFactory
     */
    private $propertyRenameFactory;

    /**
     * @var FileInfoParser
     */
    private $fileInfoParser;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var MatchPropertyTypeExpectedNameResolver
     */
    private $matchPropertyTypeExpectedNameResolver;

    protected function setUp(): void
    {
        $this->boot();

        $this->propertyRenameFactory = $this->getService(PropertyRenameFactory::class);
        $this->matchPropertyTypeExpectedNameResolver = $this->getService(
            MatchPropertyTypeExpectedNameResolver::class
        );

        $this->fileInfoParser = $this->getService(FileInfoParser::class);
        $this->betterNodeFinder = $this->getService(BetterNodeFinder::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfoWithProperty, string $expectedName, string $currentName): void
    {
        $property = $this->getPropertyFromFileInfo($fileInfoWithProperty);

        $expectedPropertyName = $this->matchPropertyTypeExpectedNameResolver->resolve($property);
        if ($expectedPropertyName === null) {
            return;
        }

        $actualPropertyRename = $this->propertyRenameFactory->createFromExpectedName($property, $expectedPropertyName);
        $this->assertNotNull($actualPropertyRename);

        /** @var PropertyRename $actualPropertyRename */
        $this->assertSame($property, $actualPropertyRename->getProperty());
        $this->assertSame($expectedName, $actualPropertyRename->getExpectedName());
        $this->assertSame($currentName, $actualPropertyRename->getCurrentName());
    }

    /**
     * @return Iterator<string[]|SmartFileInfo[]>
     */
    public function provideData(): Iterator
    {
        yield [new SmartFileInfo(__DIR__ . '/Fixture/skip_some_class.php.inc'), 'eliteManager', 'eventManager'];
    }

    private function getPropertyFromFileInfo(SmartFileInfo $fileInfo): Property
    {
        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate($fileInfo);

        $property = $this->betterNodeFinder->findFirstInstanceOf($nodes, Property::class);
        if (! $property instanceof Property) {
            throw new ShouldNotHappenException();
        }

        return $property;
    }
}
