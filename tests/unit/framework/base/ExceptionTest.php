<?php
namespace yiiunit\framework\base;

use yii\test\TestCase;
use yii\base\UserException;
use yii\base\InvalidCallException;


class ExceptionTest extends TestCase
{
	public function testToArrayWithPrevious() 
	{
		$e = new InvalidCallException('bar', 0 ,new InvalidCallException('foo'));
		$array = $e->toArray();
		$this->assertEquals('bar', $array['message']);
		$this->assertEquals('foo', $array['previous']['message']);
		
		$e = new InvalidCallException('bar', 0 ,new UserException('foo'));
		$array = $e->toArray();
		$this->assertEquals('bar', $array['message']);
		$this->assertEquals('foo', $array['previous']['message']);
	}
}
