<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Response;
use yii\web\ResponseFormatterInterface;

abstract class FormatterTest extends \yiiunit\TestCase
{
    /**
     * @var Response
     */
    public $response;
    /**
     * @var ResponseFormatterInterface
     */
    public $formatter;

    protected function setUp(): void
    {
        $this->mockApplication();
        $this->response = new Response();
        $this->formatter = $this->getFormatterInstance();
    }

    /**
     * @return ResponseFormatterInterface
     */
    abstract protected function getFormatterInstance();

    /**
     * Formatter should not format null.
     */
    public function testFormatNull(): void
    {
        $this->response->data = null;
        $this->formatter->format($this->response);
        $this->assertNull($this->response->content);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $json the expected JSON body
     * @dataProvider formatScalarDataProvider
     */
    public function testFormatScalar(mixed $data, $json): void
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEquals($json, $this->response->content);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $json the expected JSON body
     * @dataProvider formatArrayDataProvider
     */
    public function testFormatArrays(mixed $data, $json): void
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEquals($json, $this->response->content);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $json the expected JSON body
     * @dataProvider formatTraversableObjectDataProvider
     */
    public function testFormatTraversableObjects(mixed $data, $json): void
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEquals($json, $this->response->content);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $json the expected JSON body
     * @dataProvider formatObjectDataProvider
     */
    public function testFormatObjects(mixed $data, $json): void
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEquals($json, $this->response->content);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $expectedResult the expected body
     * @dataProvider formatModelDataProvider
     */
    public function testFormatModels(mixed $data, $expectedResult): void
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEquals($expectedResult, $this->response->content);
    }
}
