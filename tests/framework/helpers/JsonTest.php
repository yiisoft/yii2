<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\Json;
use yii\web\JsExpression;
use yiiunit\framework\web\Post;
use yiiunit\framework\models\JsonModel;
use yiiunit\TestCase;

/**
 * @group helpers
 * @coversDefaultClass \yii\helpers\Json
 */
class JsonTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, helper must work without Yii::$app
        $this->destroyApplication();
    }

    public function testEncode()
    {
        // Arrayable data encoding
        $dataArrayable = $this->getMockBuilder('\yii\base\Arrayable')->getMock();
        $dataArrayable->method('toArray')->willReturn([]);
        $actual = Json::encode($dataArrayable);
        $this->assertSame('[]', $actual);

        // basic data encoding
        $data = '1';
        $this->assertSame('"1"', Json::encode($data));

        // simple array encoding
        $data = [1, 2];
        $this->assertSame('[1,2]', Json::encode($data));
        $data = ['a' => 1, 'b' => 2];
        $this->assertSame('{"a":1,"b":2}', Json::encode($data));

        // simple object encoding
        $data = (object) ['a' => 1, 'b' => 2];
        $this->assertSame('{"a":1,"b":2}', Json::encode($data));

        // simple object with zero indexed keys encoding
        $data = (object) [
            0 => 1,
            1 => 2
        ];
        $default = Json::$keepObjectType;
        Json::$keepObjectType = true;
        $this->assertSame('{"0":1,"1":2}', Json::encode($data));
        Json::$keepObjectType = false;
        $this->assertSame('[1,2]', Json::encode($data));
        Json::$keepObjectType = $default;

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
            'a' => [1, new JsExpression($expression1)],
            'b' => new JsExpression($expression2),
        ];
        $this->assertSame("{\"a\":[1,$expression1],\"b\":$expression2}", Json::encode($data));

        // https://github.com/yiisoft/yii2/issues/957
        $data = (object) null;
        $this->assertSame('{}', Json::encode($data));

        // JsonSerializable
        $data = new JsonModel();
        $this->assertSame('{"json":"serializable"}', Json::encode($data));
        // https://github.com/yiisoft/yii2/issues/12043
        $data = new JsonModel();
        $data->data = [];
        $this->assertSame('[]', Json::encode($data));
        $data = new JsonModel();
        $data->data = (object) null;
        $this->assertSame('{}', Json::encode($data));

        // Generator (Only supported since PHP 5.5)
        if (PHP_VERSION_ID >= 50500) {
            $data = eval(<<<'PHP'
                return function () {
                    foreach (['a' => 1, 'b' => 2] as $name => $value) {
                        yield $name => $value;
                    }
                };
PHP
            );
            $this->assertSame('{"a":1,"b":2}', Json::encode($data()));
        }
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
        $data = (object) ['a' => 1, 'b' => 'c'];
        $this->assertSame('{"a":1,"b":"c"}', Json::htmlEncode($data));

        // expression encoding
        $expression = 'function () {}';
        $data = new JsExpression($expression);
        $this->assertSame($expression, Json::htmlEncode($data));

        // complex data
        $expression1 = 'function (a) {}';
        $expression2 = 'function (b) {}';
        $data = [
            'a' => [1, new JsExpression($expression1)],
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

        // SimpleXMLElement with empty tag
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<parent>
  <child1/>
  <child2>
    <subElement>sub</subElement>
  </child2>
</parent>';

        $document = simplexml_load_string($xml);
        $this->assertSame('{"child1":{},"child2":{"subElement":"sub"}}', Json::encode($document));

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
        $this->assertNull($actual);

        // basic data decoding
        $json = '"1"';
        $this->assertSame('1', Json::decode($json));

        // array decoding
        $json = '{"a":1,"b":2}';
        $this->assertSame(['a' => 1, 'b' => 2], Json::decode($json));

        // exception
        $json = '{"a":1,"b":2';
        $this->expectException('\yii\base\InvalidArgumentException');
        Json::decode($json);
    }

    /**
     * @expectedException \yii\base\InvalidArgumentException
     * @expectedExceptionMessage Invalid JSON data.
     * @covers ::decode
     */
    public function testDecodeInvalidParamException()
    {
        Json::decode([]);
    }

    /**
     * @covers ::decode
     */
    public function testHandleJsonError()
    {
        // basic syntax error
        try {
            $json = "{'a': '1'}";
            Json::decode($json);
        } catch (\yii\base\InvalidArgumentException $e) {
            $this->assertSame(Json::$jsonErrorMessages['JSON_ERROR_SYNTAX'], $e->getMessage());
        }

        // unsupported type since PHP 5.5
        try {
            $fp = fopen('php://stdin', 'r');
            $data = ['a' => $fp];
            Json::encode($data);
            fclose($fp);
        } catch (\yii\base\InvalidArgumentException $e) {
            if (PHP_VERSION_ID >= 50500) {
                $this->assertSame(Json::$jsonErrorMessages['JSON_ERROR_UNSUPPORTED_TYPE'], $e->getMessage());
            } else {
                $this->assertSame(Json::$jsonErrorMessages['JSON_ERROR_SYNTAX'], $e->getMessage());
            }
        }
    }

    public function testErrorSummary()
    {
        $model = new JsonModel();
        $model->name = 'not_an_integer';
        $model->addError('name', 'Error message. Here are some chars: < >');
        $model->addError('name', 'Error message. Here are even more chars: ""');
        $model->validate(null, false);
        $options = ['showAllErrors' => true];
        $expectedHtml = '["Error message. Here are some chars: < >","Error message. Here are even more chars: \"\""]';
        $this->assertEquals($expectedHtml, Json::errorSummary($model, $options));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17760
     * @covers ::encode
     */
    public function testEncodeDateTime()
    {
        $input = new \DateTime('October 12, 2014', new \DateTimeZone('UTC'));
        $output = Json::encode($input);
        $this->assertEquals('{"date":"2014-10-12 00:00:00.000000","timezone_type":3,"timezone":"UTC"}', $output);
    }

    /**
     * @covers ::encode
     */
    public function testPrettyPrint()
    {
        $defaultValue = Json::$prettyPrint;
        $input = ['a' => 1, 'b' => 2];
        $defOutput = '{"a":1,"b":2}';
        $ppOutput = "{\n    \"a\": 1,\n    \"b\": 2\n}";

        // Test unchanged options
        Json::$prettyPrint = null;
        $output = Json::encode($input, 320);
        $this->assertEquals($defOutput, $output);
        $output = Json::encode($input, 448);
        $this->assertEquals($ppOutput, $output);

        // Test pretty print enabled
        Json::$prettyPrint = true;
        $output = Json::encode($input, 320);
        $this->assertEquals($ppOutput, $output);
        $output = Json::encode($input, 448);
        $this->assertEquals($ppOutput, $output);

        // Test pretty print disabled
        Json::$prettyPrint = false;
        $output = Json::encode($input, 320);
        $this->assertEquals($defOutput, $output);
        $output = Json::encode($input, 448);
        $this->assertEquals($defOutput, $output);

        Json::$prettyPrint = $defaultValue;
    }
}
