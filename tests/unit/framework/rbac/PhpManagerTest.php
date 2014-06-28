<?php

namespace yiiunit\framework\rbac;

use Yii;

/**
 * @group rbac
 * @property ExposedPhpManager $auth
 */
class PhpManagerTest extends ManagerTestCase
{
    protected function getItemsFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-items.php';
    }

    protected function getAssignmentsFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-assignments.php';
    }

    protected function getRulesFile()
    {
        return Yii::$app->getRuntimePath() . '/rbac-rules.php';
    }

    protected function removeDataFiles()
    {
        @unlink($this->getItemsFile());
        @unlink($this->getAssignmentsFile());
        @unlink($this->getRulesFile());
    }

    protected function createManager()
    {
        return new ExposedPhpManager([
            'itemsFile' => $this->getItemsFile(),
            'assignmentsFile' => $this->getAssignmentsFile(),
            'rulesFile' => $this->getRulesFile(),
        ]);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->removeDataFiles();
        $this->auth = $this->createManager();
    }

    protected function tearDown()
    {
        $this->removeDataFiles();
        parent::tearDown();
    }

    public function testSaveLoad()
    {
        $this->prepareData();

        $items = $this->auth->items;
        $children = $this->auth->children;
        $assignments = $this->auth->assignments;
        $rules = $this->auth->rules;
        $this->auth->save();

        $this->auth = $this->createManager();
        $this->auth->load();

        $this->assertEquals($items, $this->auth->items);
        $this->assertEquals($children, $this->auth->children);
        $this->assertEquals($assignments, $this->auth->assignments);
        $this->assertEquals($rules, $this->auth->rules);
    }
} 