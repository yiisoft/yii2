<?php


namespace yiiunit\framework\helpers;

use yii\helpers\Json;
use yii\test\TestCase;
use yii\web\JsExpression;

/**
 * @group helpers
 */
class JsonTest extends TestCase
{
	public function testEncode()
	{
		// basic data encoding
		$data = '1';
		$this->assertSame('"1"', Json::encode($data));

		// simple array encoding
		$data = array(1, 2);
		$this->assertSame('[1,2]', Json::encode($data));
		$data = array('a' => 1, 'b' => 2);
		$this->assertSame('{"a":1,"b":2}', Json::encode($data));

		// simple object encoding
		$data = new \stdClass();
		$data->a = 1;
		$data->b = 2;
		$this->assertSame('{"a":1,"b":2}', Json::encode($data));

		// expression encoding
		$expression = 'function () {}';
		$data = new JsExpression($expression);
		$this->assertSame($expression, Json::encode($data));

		// complex data
		$expression1 = 'function (a) {}';
		$expression2 = 'function (b) {}';
		$data = array(
			'a' => array(
				1, new JsExpression($expression1)
			),
			'b' => new JsExpression($expression2),
		);
		$this->assertSame("{\"a\":[1,$expression1],\"b\":$expression2}", Json::encode($data));

		// https://github.com/yiisoft/yii2/issues/957
		$data = (object)null;
		$this->assertSame('{}', Json::encode($data));
	}

	public function testDecode()
	{
		// basic data decoding
		$json = '"1"';
		$this->assertSame('1', Json::decode($json));

		// array decoding
		$json = '{"a":1,"b":2}';
		$this->assertSame(array('a' => 1, 'b' => 2), Json::decode($json));

		// exception
		$json = '{"a":1,"b":2';
		$this->setExpectedException('yii\base\InvalidParamException');
		Json::decode($json);
	}
}
