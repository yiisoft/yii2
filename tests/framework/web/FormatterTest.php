<?php


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

    protected function setUp()
    {
        $this->mockApplication();
        $this->response = new Response;
        $this->formatter = $this->getFormatterInstance();
    }

    /**
     * @return ResponseFormatterInterface
     */
    abstract protected function getFormatterInstance();

    /**
     * Formatter should not format null
     */
    public function testFormatNull()
    {
        $this->response->data = null;
        $this->formatter->format($this->response);
        $this->assertEquals(null, $this->response->content);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $json the expected JSON body
     * @dataProvider formatScalarDataProvider
     */
    public function testFormatScalar($data, $json)
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
    public function testFormatArrays($data, $json)
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
    public function testFormatObjects($data, $json)
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEquals($json, $this->response->content);
    }
}