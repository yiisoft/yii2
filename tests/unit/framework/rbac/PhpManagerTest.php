<?php

namespace yii\rbac;

/**
 * Mock for the filemtime() function for rbac classes. Avoid random test fails.
 * @return int
 */
function filemtime($file)
{
    return \yiiunit\framework\rbac\PhpManagerTest::$filemtime ?: \filemtime($file);
}

namespace yiiunit\framework\rbac;

use Yii;

/**
 * @group rbac
 * @property ExposedPhpManager $auth
 */
class PhpManagerTest extends ManagerTestCase
{
    public static $filemtime;

    protected function getItemFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-items.php';
    }

    protected function getAssignmentFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-assignments.php';
    }

    protected function getRuleFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-rules.php';
    }

    protected function removeDataFiles()
    {
        @unlink($this->getItemFile());
        @unlink($this->getAssignmentFile());
        @unlink($this->getRuleFile());
    }

    /**
     * @inheritdoc
     */
    protected function createManager()
    {
        return new ExposedPhpManager([
            'itemFile' => $this->getItemFile(),
            'assignmentFile' => $this->getAssignmentFile(),
            'ruleFile' => $this->getRuleFile(),
        ]);
    }

    protected function setUp()
    {
        static::$filemtime = null;
        parent::setUp();
        $this->mockApplication();
        $this->removeDataFiles();
        $this->auth = $this->createManager();
    }

    protected function tearDown()
    {
        $this->removeDataFiles();
        static::$filemtime = null;
        parent::tearDown();
    }

    public function testSaveLoad()
    {
        $this->prepareData();

        $items = $this->auth->items;
        $children = $this->auth->children;
        $assignments = $this->auth->assignments;
        $rules = $this->auth->rules;
        static::$filemtime = time();
        $this->auth->save();

        $this->auth = $this->createManager();
        $this->auth->load();

        $this->assertEquals($items, $this->auth->items);
        $this->assertEquals($children, $this->auth->children);
        $this->assertEquals($assignments, $this->auth->assignments);
        $this->assertEquals($rules, $this->auth->rules);
    }
}
