<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace {
    if (!function_exists('apc_delete_file')) {
        function apc_delete_file($file)
        {
            return true;
        }
    }
}

namespace yii\rbac {
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
}

namespace yiiunit\framework\rbac {

    use Yii;
    use yii\base\InvalidArgumentException;
    use yii\base\InvalidCallException;
    use yii\rbac\Item;
    use yii\rbac\Permission;
    use yii\rbac\Role;

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

        public function testSaveLoad(): void
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

        public function testUpdateItemName(): void
        {
            $this->prepareData();

            $name = 'readPost';
            $permission = $this->auth->getPermission($name);
            $permission->name = 'UPDATED-NAME';
            $this->assertTrue($this->auth->update($name, $permission), 'You should be able to update name.');
        }

        public function testUpdateDescription(): void
        {
            $this->prepareData();
            $name = 'readPost';
            $permission = $this->auth->getPermission($name);
            $permission->description = 'UPDATED-DESCRIPTION';
            $this->assertTrue($this->auth->update($name, $permission), 'You should be able to save w/o changing name.');
        }

        public function testOverwriteName(): void
        {
            $this->prepareData();

            $name = 'readPost';
            $permission = $this->auth->getPermission($name);
            $permission->name = 'createPost';

            $this->expectException('yii\base\InvalidParamException');

            $this->auth->update($name, $permission);
        }

        public function testSaveAssignments(): void
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

        public function testCheckAccessReturnsFalseWithoutAssignmentsAndDefaultRoles(): void
        {
            $manager = $this->createPhpManager(['defaultRoles' => []]);

            $this->assertFalse($manager->checkAccess('guest', 'missing'));
        }

        public function testAddChildThrowsExceptionWhenItemsDoNotExist(): void
        {
            $parent = $this->auth->createRole('parent');
            $child = $this->auth->createPermission('child');

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("Either 'parent' or 'child' does not exist.");

            $this->auth->addChild($parent, $child);
        }

        public function testAddChildThrowsExceptionWhenChildAlreadyExists(): void
        {
            $author = $this->auth->createRole('author');
            $reader = $this->auth->createRole('reader');
            $this->auth->add($author);
            $this->auth->add($reader);
            $this->auth->addChild($author, $reader);

            $this->expectException(InvalidCallException::class);
            $this->expectExceptionMessage("The item 'author' already has a child 'reader'.");

            $this->auth->addChild($author, $reader);
        }

        public function testAssignThrowsExceptionForUnknownItem(): void
        {
            $role = new Role(['name' => 'ghost']);

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("Unknown role 'ghost'.");

            $this->auth->assign($role, 7);
        }

        public function testAssignThrowsExceptionForDuplicateAssignment(): void
        {
            $role = $this->auth->createRole('reader');
            $this->auth->add($role);
            $this->auth->assign($role, 7);

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("Authorization item 'reader' has already been assigned to user '7'.");

            $this->auth->assign($role, 7);
        }

        public function testRevokeReturnsFalseWhenAssignmentDoesNotExist(): void
        {
            $role = $this->auth->createRole('reader');
            $this->auth->add($role);

            $this->assertFalse($this->auth->revoke($role, 42));
        }

        public function testRevokeAllReturnsExpectedState(): void
        {
            $reader = $this->auth->createRole('reader');
            $author = $this->auth->createRole('author');
            $this->auth->add($reader);
            $this->auth->add($author);
            $this->auth->assign($reader, 7);
            $this->auth->assign($author, 7);

            $this->assertTrue($this->auth->revokeAll(7));
            $this->assertEmpty($this->auth->getAssignments(7));
            $this->assertTrue($this->auth->revokeAll(7));
        }

        public function testRevokeAllReturnsFalseWhenUserHasNoAssignments(): void
        {
            $this->assertFalse($this->auth->revokeAll(99));
        }

        public function testGetPermissionsByUserReturnsOnlyDirectPermissions(): void
        {
            $permission = $this->auth->createPermission('editPost');
            $this->auth->add($permission);
            $this->auth->assign($permission, 7);

            $permissions = $this->auth->getPermissionsByUser(7);

            $this->assertArrayHasKey('editPost', $permissions);
            $this->assertCount(1, $permissions);
        }

        public function testRemoveItemReturnsFalseWhenItemDoesNotExist(): void
        {
            $role = new Role(['name' => 'ghost']);

            $this->assertFalse($this->auth->remove($role));
        }

        public function testRemoveAllAssignmentsClearsSavedAssignments(): void
        {
            $role = $this->auth->createRole('reader');
            $this->auth->add($role);
            $this->auth->assign($role, 7);

            $this->auth->removeAllAssignments();

            $this->assertSame([], $this->auth->getAssignments(7));
        }

        public function testRemoveReturnsFalseForUnknownRule(): void
        {
            $this->assertFalse($this->auth->remove(new ActionRule(['name' => 'missing'])));
        }

        public function testUpdateRenamesChildrenAndAssignments(): void
        {
            $parent = $this->auth->createRole('parent');
            $child = $this->auth->createRole('child');
            $permission = $this->auth->createPermission('editPost');
            $this->auth->add($parent);
            $this->auth->add($child);
            $this->auth->add($permission);
            $this->auth->addChild($parent, $child);
            $this->auth->addChild($child, $permission);
            $this->auth->assign($child, 9);

            $child->name = 'renamedChild';
            $this->auth->update('child', $child);

            $this->assertTrue($this->auth->hasChild($parent, $child));
            $this->assertArrayHasKey('editPost', $this->auth->getChildren('renamedChild'));
            $this->assertNull($this->auth->getAssignment('child', 9));
            $this->assertNotNull($this->auth->getAssignment('renamedChild', 9));
        }

        private function createPhpManager(array $config = []): ExposedPhpManager
        {
            return new ExposedPhpManager(array_merge([
            'itemFile' => $this->getItemFile(),
            'assignmentFile' => $this->getAssignmentFile(),
            'ruleFile' => $this->getRuleFile(),
            'defaultRoles' => ['myDefaultRole'],
            ], $config));
        }
    }
}
