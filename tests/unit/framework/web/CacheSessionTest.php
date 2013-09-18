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

	public function testCreate()
	{
		$session = new CacheSession();

		$session->writeSession('test', 'sessionData');
		$this->assertEquals('sessionData', $session->readSession('test'));
	}
}
