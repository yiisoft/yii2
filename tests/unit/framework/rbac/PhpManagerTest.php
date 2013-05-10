<?php

namespace yiiunit\framework\rbac;

use yii\rbac\PhpManager;

require_once(__DIR__ . '/ManagerTestBase.php');

class PhpManagerTest extends ManagerTestBase
{
	public function setUp()
	{
		$authFile = \Yii::$app->getRuntimePath() . '/rbac.php';
		@unlink($authFile);
		$this->auth = new PhpManager;
		$this->auth->authFile = $authFile;
		$this->auth->init();
		$this->prepareData();
	}

	public function tearDown()
	{
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
