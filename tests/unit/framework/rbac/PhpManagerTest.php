<?php

namespace yiiunit\framework\rbac;

use Yii;
use yii\rbac\PhpManager;

//require_once(__DIR__ . '/ManagerTestBase.php');

class PhpManagerTest extends ManagerTestBase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
		$authFile = Yii::$app->getRuntimePath() . '/rbac.php';
		@unlink($authFile);
		$this->auth = new PhpManager;
		$this->auth->authFile = $authFile;
		$this->auth->init();
		$this->prepareData();
	}

	protected function tearDown()
	{
		parent::tearDown();
		@unlink($this->auth->authFile);
	}

	public function testSaveLoad()
	{
		$this->auth->save();
		$this->auth->clearAll();
		$this->auth->load();
		$this->testCheckAccess();
	}
}
