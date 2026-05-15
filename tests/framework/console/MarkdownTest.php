<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\console;

use yii\console\Markdown;
use yii\helpers\Console;
use yiiunit\TestCase;

/**
 * @group console
 */
class MarkdownTest extends TestCase
{
    /**
     * @var Markdown
     */
    private $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->destroyApplication();
        $this->parser = new Markdown();
    }

    public function testRenderStrong(): void
    {
        $result = $this->parser->parse('**bold**');
        $stripped = Console::stripAnsiFormat($result);
        $this->assertStringContainsString('bold', $stripped);
    }

    public function testRenderEmph(): void
    {
        $result = $this->parser->parse('*italic*');
        $stripped = Console::stripAnsiFormat($result);
        $this->assertStringContainsString('italic', $stripped);
    }

    public function testRenderInlineCode(): void
    {
        $result = $this->parser->parse('use `code` here');
        $stripped = Console::stripAnsiFormat($result);
        $this->assertStringContainsString('code', $stripped);
    }

    public function testRenderCodeBlock(): void
    {
        $result = $this->parser->parse("```\necho 'hello';\n```");
        $stripped = Console::stripAnsiFormat($result);
        $this->assertStringContainsString('echo', $stripped);
    }

    public function testRenderParagraph(): void
    {
        $result = $this->parser->parse("First paragraph.\n\nSecond paragraph.");
        $stripped = Console::stripAnsiFormat($result);
        $this->assertStringContainsString('First paragraph.', $stripped);
        $this->assertStringContainsString('Second paragraph.', $stripped);
    }
}
