<?php

namespace yiiunit\framework\rbac;

use Yii;
use yii\rbac\PhpManager;

/**
 * @group rbac
 * @property \yii\rbac\PhpManager $auth
 */
class PhpManagerTest extends ManagerTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $authFile = Yii::$app->getRuntimePath() . '/rbac.php';
        @unlink($authFile);
        $this->auth = new PhpManager();
        $this->auth->authFile = $authFile;
        $this->auth->init();
    }

    protected function tearDown()
    {
        parent::tearDown();
        @unlink($this->auth->authFile);
    }

    public function testSaveLoad()
    {
        $this->prepareData();
        $this->auth->save();
        $this->auth->removeAll();
        $this->auth->load();
        // TODO : Check if loaded and saved data are the same.
    }

} 