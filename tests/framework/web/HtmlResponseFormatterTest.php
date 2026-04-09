<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web;

use yii\web\HtmlResponseFormatter;
use yii\web\Response;
use yiiunit\TestCase;

/**
 * @group web
 */
class HtmlResponseFormatterTest extends TestCase
{
    /**
     * @var Response
     */
    private $response;

    protected function setUp(): void
    {
        $this->mockApplication();
        $this->response = new Response();
        $this->response->charset = 'UTF-8';
    }

    protected function tearDown(): void
    {
        $this->destroyApplication();
        parent::tearDown();
    }

    public function testDefaultContentType(): void
    {
        $formatter = new HtmlResponseFormatter();

        $this->assertSame('text/html', $formatter->contentType);
    }

    public function testAppendsCharsetToContentType(): void
    {
        $formatter = new HtmlResponseFormatter();
        $this->response->data = '<p>test</p>';
        $formatter->format($this->response);

        $this->assertSame(
            'text/html; charset=UTF-8',
            $this->response->getHeaders()->get('Content-Type')
        );
    }

    public function testPreservesExistingCharsetInContentType(): void
    {
        $formatter = new HtmlResponseFormatter([
            'contentType' => 'text/html; charset=ISO-8859-1',
        ]);
        $this->response->data = '<p>test</p>';
        $formatter->format($this->response);

        $this->assertSame(
            'text/html; charset=ISO-8859-1',
            $this->response->getHeaders()->get('Content-Type')
        );
    }

    public function testUsesResponseCharset(): void
    {
        $formatter = new HtmlResponseFormatter();
        $this->response->charset = 'Windows-1251';
        $this->response->data = '<p>test</p>';
        $formatter->format($this->response);

        $this->assertSame(
            'text/html; charset=Windows-1251',
            $this->response->getHeaders()->get('Content-Type')
        );
    }

    public function testCustomContentType(): void
    {
        $formatter = new HtmlResponseFormatter([
            'contentType' => 'application/xhtml+xml',
        ]);
        $this->response->data = '<p>test</p>';
        $formatter->format($this->response);

        $this->assertSame(
            'application/xhtml+xml; charset=UTF-8',
            $this->response->getHeaders()->get('Content-Type')
        );
    }

    public function testCopiesDataToContent(): void
    {
        $formatter = new HtmlResponseFormatter();
        $this->response->data = '<h1>Hello</h1>';
        $formatter->format($this->response);

        $this->assertSame('<h1>Hello</h1>', $this->response->content);
    }

    public function testNullDataDoesNotOverwriteContent(): void
    {
        $formatter = new HtmlResponseFormatter();
        $this->response->content = '<p>existing</p>';
        $this->response->data = null;
        $formatter->format($this->response);

        $this->assertSame('<p>existing</p>', $this->response->content);
    }

    public function testCharsetAppendedOnlyOnce(): void
    {
        $formatter = new HtmlResponseFormatter();
        $this->response->data = 'first';
        $formatter->format($this->response);

        $this->response->data = 'second';
        $formatter->format($this->response);

        $this->assertSame(
            'text/html; charset=UTF-8',
            $this->response->getHeaders()->get('Content-Type')
        );
    }

    public function testCharsetCheckIsCaseInsensitive(): void
    {
        $formatter = new HtmlResponseFormatter([
            'contentType' => 'text/html; Charset=UTF-8',
        ]);
        $this->response->data = '<p>test</p>';
        $formatter->format($this->response);

        $this->assertSame(
            'text/html; Charset=UTF-8',
            $this->response->getHeaders()->get('Content-Type')
        );
    }
}
