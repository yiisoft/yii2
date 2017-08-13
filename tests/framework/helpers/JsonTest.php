<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\base\Model;
use yii\helpers\BaseJson;
use yii\helpers\Json;
use yii\web\JsExpression;
use yiiunit\framework\web\Post;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class JsonTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Helper must work without Yii::$app
        $this->destroyApplication();
    }

    public function testEncode()
    {
        // Arrayable data encoding
        $dataArrayable = $this->getMock('yii\\base\\Arrayable');
        $dataArrayable->method('toArray')->willReturn([]);
        $actual = Json::encode($dataArrayable);
        $this->assertSame('{}', $actual);

        // basic data encoding
        $data = '1';
        $this->assertSame('"1"', Json::encode($data));

        // simple array encoding
        $data = [1, 2];
        $this->assertSame('[1,2]', Json::encode($data));
        $data = ['a' => 1, 'b' => 2];
        $this->assertSame('{"a":1,"b":2}', Json::encode($data));

        // simple object encoding
        $data = new \stdClass();
        $data->a = 1;
        $data->b = 2;
        $this->assertSame('{"a":1,"b":2}', Json::encode($data));

        // empty data encoding
        $data = [];
        $this->assertSame('[]', Json::encode($data));
        $data = new \stdClass();
        $this->assertSame('{}', Json::encode($data));

        // expression encoding
        $expression = 'function () {}';
        $data = new JsExpression($expression);
        $this->assertSame($expression, Json::encode($data));

        // complex data
        $expression1 = 'function (a) {}';
        $expression2 = 'function (b) {}';
        $data = [
            'a' => [
                1, new JsExpression($expression1),
            ],
            'b' => new JsExpression($expression2),
        ];
        $this->assertSame("{\"a\":[1,$expression1],\"b\":$expression2}", Json::encode($data));

        // https://github.com/yiisoft/yii2/issues/957
        $data = (object) null;
        $this->assertSame('{}', Json::encode($data));

        // JsonSerializable
        $data = new JsonModel();
        $this->assertSame('{"json":"serializable"}', Json::encode($data));
        // @see https://github.com/yiisoft/yii2/issues/12043
        $data = new JsonModel();
        $data->data = [];
        $this->assertSame('[]', Json::encode($data));
        $data = new JsonModel();
        $data->data = (object) null;
        $this->assertSame('{}', Json::encode($data));
    }

    public function testHtmlEncode()
    {
        // HTML escaped chars
        $data = '&<>"\'/';
        $this->assertSame('"\u0026\u003C\u003E\u0022\u0027\/"', Json::htmlEncode($data));

        // basic data encoding
        $data = '1';
        $this->assertSame('"1"', Json::htmlEncode($data));

        // simple array encoding
        $data = [1, 2];
        $this->assertSame('[1,2]', Json::htmlEncode($data));
        $data = ['a' => 1, 'b' => 2];
        $this->assertSame('{"a":1,"b":2}', Json::htmlEncode($data));

        // simple object encoding
        $data = new \stdClass();
        $data->a = 1;
        $data->b = 2;
        $this->assertSame('{"a":1,"b":2}', Json::htmlEncode($data));

        // expression encoding
        $expression = 'function () {}';
        $data = new JsExpression($expression);
        $this->assertSame($expression, Json::htmlEncode($data));

        // complex data
        $expression1 = 'function (a) {}';
        $expression2 = 'function (b) {}';
        $data = [
            'a' => [
                1, new JsExpression($expression1),
            ],
            'b' => new JsExpression($expression2),
        ];
        $this->assertSame("{\"a\":[1,$expression1],\"b\":$expression2}", Json::htmlEncode($data));

        // https://github.com/yiisoft/yii2/issues/957
        $data = (object) null;
        $this->assertSame('{}', Json::htmlEncode($data));

        // JsonSerializable
        $data = new JsonModel();
        $this->assertSame('{"json":"serializable"}', Json::htmlEncode($data));

        // https://github.com/yiisoft/yii2/issues/10278
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<file>
  <apiKey>ieu2iqw4o</apiKey>
  <methodProperties>
    <FindByString>Kiev</FindByString>
  </methodProperties>
</file>';

        $document = simplexml_load_string($xml);
        $this->assertSame('{"apiKey":"ieu2iqw4o","methodProperties":{"FindByString":"Kiev"}}', Json::encode($document));

        $postsStack = new \SplStack();
        $postsStack->push(new Post(915, 'record1'));
        $postsStack->push(new Post(456, 'record2'));

        $this->assertSame('{"1":{"id":456,"title":"record2"},"0":{"id":915,"title":"record1"}}', Json::encode($postsStack));
    }

    public function testDecode()
    {
        // empty value
        $json = '';
        $actual = Json::decode($json);
        $this->assertSame(null, $actual);

        // basic data decoding
        $json = '"1"';
        $this->assertSame('1', Json::decode($json));

        // array decoding
        $json = '{"a":1,"b":2}';
        $this->assertSame(['a' => 1, 'b' => 2], Json::decode($json));

        // exception
        $json = '{"a":1,"b":2';
        $this->expectException('yii\base\InvalidParamException');
        Json::decode($json);
    }

    /**
     * @expectedException \yii\base\InvalidParamException
     * @expectedExceptionMessage Invalid JSON data.
     */
    public function testDecodeInvalidParamException()
    {
        Json::decode([]);
    }

    public function testHandleJsonError()
    {
        // Basic syntax error
        try {
            $json = "{'a': '1'}";
            Json::decode($json);
        } catch (\yii\base\InvalidParamException $e) {
            $this->assertSame(BaseJson::$jsonErrorMessages['JSON_ERROR_SYNTAX'], $e->getMessage());
        }

        // Unsupported type since PHP 5.5
        try {
            $fp = fopen('php://stdin', 'r');
            $data = ['a' => $fp];
            Json::encode($data);
            fclose($fp);
        } catch (\yii\base\InvalidParamException $e) {
            if (PHP_VERSION_ID >= 50500) {
                $this->assertSame(BaseJson::$jsonErrorMessages['JSON_ERROR_UNSUPPORTED_TYPE'], $e->getMessage());
            } else {
                $this->assertSame(BaseJson::$jsonErrorMessages['JSON_ERROR_SYNTAX'], $e->getMessage());
            }
        }
    }
}

class JsonModel extends Model implements \JsonSerializable
{
    public $data = ['json' => 'serializable'];

    public function jsonSerialize()
    {
        return $this->data;
    }
}
