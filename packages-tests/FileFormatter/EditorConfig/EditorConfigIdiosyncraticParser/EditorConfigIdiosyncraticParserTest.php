<?php

namespace Rector\Tests\FileFormatter\EditorConfig\EditorConfigIdiosyncraticParser;

use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Contract\EditorConfig\EditorConfigParserInterface;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class EditorConfigIdiosyncraticParserTest extends AbstractTestCase
{
    /**
     * @var EditorConfigParserInterface
     */
    private $editorConfigParser;

    protected function setUp(): void
    {
        $this->boot();
        $this->editorConfigParser = $this->getService(EditorConfigParserInterface::class);
    }

    public function testComposerJsonFile(): void
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withIndent(Indent::createSpaceWithSize(20));

        $composerJsonFile = new SmartFileInfo(__DIR__ . '/Fixture/composer.json');

        $file = new File($composerJsonFile, $composerJsonFile->getContents());

        $editorConfigConfiguration = $this->editorConfigParser->extractConfigurationForFile(
            $file,
            $editorConfigConfigurationBuilder
        );

        $this->assertSame('tab', $editorConfigConfiguration->getIndentStyle());
        $this->assertSame(1, $editorConfigConfiguration->getIndentSize());
    }
}
