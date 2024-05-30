<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\rbac;

/**
 * Mock for the filemtime() function for rbac classes. Avoid random test fails.
 * @param string $file
 * @return int
 */
function filemtime($file)
{
    return \yiiunit\framework\rbac\PhpManagerTest::$filemtime ?: \filemtime($file);
}

/**
 * Mock for the time() function for rbac classes. Avoid random test fails.
 * @return int
 */
function time()
{
    return \yiiunit\framework\rbac\PhpManagerTest::$time ?: \time();
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
    public static $time;

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
     * {@inheritdoc}
     */
    protected function createManager()
    {
        return new ExposedPhpManager([
            'itemFile' => $this->getItemFile(),
            'assignmentFile' => $this->getAssignmentFile(),
            'ruleFile' => $this->getRuleFile(),
            'defaultRoles' => ['myDefaultRole'],
        ]);
    }

    protected function setUp(): void
    {
        static::$filemtime = null;
        static::$time = null;
        parent::setUp();

        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('PhpManager is not compatible with HHVM.');
        }

        $this->mockApplication();
        $this->removeDataFiles();
        $this->auth = $this->createManager();
    }

    protected function tearDown(): void
    {
        $this->removeDataFiles();
        static::$filemtime = null;
        static::$time = null;
        parent::tearDown();
    }

    public function testSaveLoad()
    {
        static::$time = static::$filemtime = \time();

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

    public function testUpdateItemName()
    {
        $this->prepareData();

        $name = 'readPost';
        $permission = $this->auth->getPermission($name);
        $permission->name = 'UPDATED-NAME';
        $this->assertTrue($this->auth->update($name, $permission), 'You should be able to update name.');
    }

    public function testUpdateDescription()
    {
        $this->prepareData();
        $name = 'readPost';
        $permission = $this->auth->getPermission($name);
        $permission->description = 'UPDATED-DESCRIPTION';
        $this->assertTrue($this->auth->update($name, $permission), 'You should be able to save w/o changing name.');
    }

    public function testOverwriteName()
    {
        $this->prepareData();

        $name = 'readPost';
        $permission = $this->auth->getPermission($name);
        $permission->name = 'createPost';

        $this->expectException('yii\base\InvalidParamException');

        $this->auth->update($name, $permission);
    }

    public function testSaveAssignments()
    {
        $this->auth->removeAll();
        $role = $this->auth->createRole('Admin');
        $this->auth->add($role);
        $this->auth->assign($role, 13);
        $this->assertStringContainsString('Admin', file_get_contents($this->getAssignmentFile()));
        $role->name = 'NewAdmin';
        $this->auth->update('Admin', $role);
        $this->assertStringContainsString('NewAdmin', file_get_contents($this->getAssignmentFile()));
        $this->auth->remove($role);
        $this->assertStringNotContainsString('NewAdmin', file_get_contents($this->getAssignmentFile()));
    }
}
