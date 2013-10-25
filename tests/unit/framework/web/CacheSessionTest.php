<?php

namespace yiiunit\framework\web;

use Yii;
use yii\caching\FileCache;
use yii\web\CacheSession;

/**
 * @group web
 */
class CacheSessionTest extends \yiiunit\TestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
		Yii::$app->setComponent('cache', new FileCache());
	}

	public function testCacheSession()
	{
		$session = new CacheSession();

		$session->writeSession('test', 'sessionData');
		$this->assertEquals('sessionData', $session->readSession('test'));
		$session->destroySession('test');
		$this->assertEquals('', $session->readSession('test'));
	}

	public function testInvalidCache()
	{
		$this->setExpectedException('yii\base\InvalidConfigException');
		$session = new CacheSession(['cache' => 'invalid']);
	}
}
