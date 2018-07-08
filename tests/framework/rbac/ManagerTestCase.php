<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use yii\base\InvalidArgumentException;
use yii\rbac\BaseManager;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;
use yiiunit\TestCase;

/**
 * ManagerTestCase.
 */
abstract class ManagerTestCase extends TestCase
{
    /**
     * @var \yii\rbac\ManagerInterface|BaseManager
     */
    protected $auth;

    /**
     * @return \yii\rbac\ManagerInterface
     */
    abstract protected function createManager();

    public function testCreateRole()
    {
        $role = $this->auth->createRole('admin');
        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals(Item::TYPE_ROLE, $role->type);
        $this->assertEquals('admin', $role->name);
    }

    public function testCreatePermission()
    {
        $permission = $this->auth->createPermission('edit post');
        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals(Item::TYPE_PERMISSION, $permission->type);
        $this->assertEquals('edit post', $permission->name);
    }

    public function testAdd()
    {
        $role = $this->auth->createRole('admin');
        $role->description = 'administrator';
        $this->assertTrue($this->auth->add($role));

        $permission = $this->auth->createPermission('edit post');
        $permission->description = 'edit a post';
        $this->assertTrue($this->auth->add($permission));

        $rule = new AuthorRule(['name' => 'is author', 'reallyReally' => true]);
        $this->assertTrue($this->auth->add($rule));

        // todo: check duplication of name
    }

    public function testGetChildren()
    {
        $user = $this->auth->createRole('user');
        $this->auth->add($user);
        $this->assertCount(0, $this->auth->getChildren($user->name));

        $changeName = $this->auth->createPermission('changeName');
        $this->auth->add($changeName);
        $this->auth->addChild($user, $changeName);
        $this->assertCount(1, $this->auth->getChildren($user->name));
    }

    public function testGetRule()
    {
        $this->prepareData();

        $rule = $this->auth->getRule('isAuthor');
        $this->assertInstanceOf('yii\rbac\Rule', $rule);
        $this->assertEquals('isAuthor', $rule->name);

        $rule = $this->auth->getRule('nonExisting');
        $this->assertNull($rule);
    }

    public function testAddRule()
    {
        $this->prepareData();

        $ruleName = 'isReallyReallyAuthor';
        $rule = new AuthorRule(['name' => $ruleName, 'reallyReally' => true]);
        $this->auth->add($rule);

        $rule = $this->auth->getRule($ruleName);
        $this->assertEquals($ruleName, $rule->name);
        $this->assertTrue($rule->reallyReally);
    }

    public function testUpdateRule()
    {
        $this->prepareData();

        $rule = $this->auth->getRule('isAuthor');
        $rule->name = 'newName';
        $rule->reallyReally = false;
        $this->auth->update('isAuthor', $rule);

        $rule = $this->auth->getRule('isAuthor');
        $this->assertNull($rule);

        $rule = $this->auth->getRule('newName');
        $this->assertEquals('newName', $rule->name);
        $this->assertFalse($rule->reallyReally);

        $rule->reallyReally = true;
        $this->auth->update('newName', $rule);

        $rule = $this->auth->getRule('newName');
        $this->assertTrue($rule->reallyReally);

        $item = $this->auth->getPermission('createPost');
        $item->name = 'new createPost';
        $this->auth->update('createPost', $item);

        $item = $this->auth->getPermission('createPost');
        $this->assertNull($item);

        $item = $this->auth->getPermission('new createPost');
        $this->assertEquals('new createPost', $item->name);
    }

    public function testGetRules()
    {
        $this->prepareData();

        $rule = new AuthorRule(['name' => 'isReallyReallyAuthor', 'reallyReally' => true]);
        $this->auth->add($rule);

        $rules = $this->auth->getRules();

        $ruleNames = [];
        foreach ($rules as $rule) {
            $ruleNames[] = $rule->name;
        }

        $this->assertContains('isReallyReallyAuthor', $ruleNames);
        $this->assertContains('isAuthor', $ruleNames);
    }

    public function testRemoveRule()
    {
        $this->prepareData();

        $this->auth->remove($this->auth->getRule('isAuthor'));
        $rules = $this->auth->getRules();

        $this->assertEmpty($rules);

        $this->auth->remove($this->auth->getPermission('createPost'));
        $item = $this->auth->getPermission('createPost');
        $this->assertNull($item);
    }

    public function testCheckAccess()
    {
        $this->prepareData();

        $testSuites = [
            'reader A' => [
                'createPost' => false,
                'readPost' => true,
                'updatePost' => false,
                'updateAnyPost' => false,
            ],
            'author B' => [
                'createPost' => true,
                'readPost' => true,
                'updatePost' => true,
                'deletePost' => true,
                'updateAnyPost' => false,
            ],
            'admin C' => [
                'createPost' => true,
                'readPost' => true,
                'updatePost' => false,
                'updateAnyPost' => true,
                'blablabla' => false,
                null => false,
            ],
            'guest' => [
                // all actions denied for guest (user not exists)
                'createPost' => false,
                'readPost' => false,
                'updatePost' => false,
                'deletePost' => false,
                'updateAnyPost' => false,
                'blablabla' => false,
                null => false,
            ],
        ];

        $params = ['authorID' => 'author B'];

        foreach ($testSuites as $user => $tests) {
            foreach ($tests as $permission => $result) {
                $this->assertEquals($result, $this->auth->checkAccess($user, $permission, $params), "Checking $user can $permission");
            }
        }
    }

    protected function prepareData()
    {
        $rule = new AuthorRule();
        $this->auth->add($rule);

        $uniqueTrait = $this->auth->createPermission('Fast Metabolism');
        $uniqueTrait->description = 'Your metabolic rate is twice normal. This means that you are much less resistant to radiation and poison, but your body heals faster.';
        $this->auth->add($uniqueTrait);

        $createPost = $this->auth->createPermission('createPost');
        $createPost->data = 'createPostData';
        $createPost->description = 'create a post';
        $this->auth->add($createPost);

        $readPost = $this->auth->createPermission('readPost');
        $readPost->description = 'read a post';
        $this->auth->add($readPost);

        $deletePost = $this->auth->createPermission('deletePost');
        $deletePost->description = 'delete a post';
        $this->auth->add($deletePost);

        $updatePost = $this->auth->createPermission('updatePost');
        $updatePost->description = 'update a post';
        $updatePost->ruleName = $rule->name;
        $this->auth->add($updatePost);

        $updateAnyPost = $this->auth->createPermission('updateAnyPost');
        $updateAnyPost->description = 'update any post';
        $this->auth->add($updateAnyPost);

        $withoutChildren = $this->auth->createRole('withoutChildren');
        $this->auth->add($withoutChildren);

        $reader = $this->auth->createRole('reader');
        $this->auth->add($reader);
        $this->auth->addChild($reader, $readPost);

        $author = $this->auth->createRole('author');
        $author->data = 'authorData';
        $this->auth->add($author);
        $this->auth->addChild($author, $createPost);
        $this->auth->addChild($author, $updatePost);
        $this->auth->addChild($author, $reader);

        $admin = $this->auth->createRole('admin');
        $this->auth->add($admin);
        $this->auth->addChild($admin, $author);
        $this->auth->addChild($admin, $updateAnyPost);

        $this->auth->assign($uniqueTrait, 'reader A');

        $this->auth->assign($reader, 'reader A');
        $this->auth->assign($author, 'author B');
        $this->auth->assign($deletePost, 'author B');
        $this->auth->assign($admin, 'admin C');
    }

    public function testGetPermissionsByRole()
    {
        $this->prepareData();
        $permissions = $this->auth->getPermissionsByRole('admin');
        $expectedPermissions = ['createPost', 'updatePost', 'readPost', 'updateAnyPost'];
        $this->assertEquals(count($expectedPermissions), count($permissions));
        foreach ($expectedPermissions as $permissionName) {
            $this->assertInstanceOf(Permission::class, $permissions[$permissionName]);
        }
    }

    public function testGetPermissionsByUser()
    {
        $this->prepareData();
        $permissions = $this->auth->getPermissionsByUser('author B');
        $expectedPermissions = ['deletePost', 'createPost', 'updatePost', 'readPost'];
        $this->assertEquals(count($expectedPermissions), count($permissions));
        foreach ($expectedPermissions as $permissionName) {
            $this->assertInstanceOf(Permission::class, $permissions[$permissionName]);
        }
    }

    public function testGetRole()
    {
        $this->prepareData();
        $author = $this->auth->getRole('author');
        $this->assertEquals(Item::TYPE_ROLE, $author->type);
        $this->assertEquals('author', $author->name);
        $this->assertEquals('authorData', $author->data);
    }

    public function testGetPermission()
    {
        $this->prepareData();
        $createPost = $this->auth->getPermission('createPost');
        $this->assertEquals(Item::TYPE_PERMISSION, $createPost->type);
        $this->assertEquals('createPost', $createPost->name);
        $this->assertEquals('createPostData', $createPost->data);
    }

    public function testGetRolesByUser()
    {
        $this->prepareData();
        $reader = $this->auth->getRole('reader');
        $this->auth->assign($reader, 0);
        $this->auth->assign($reader, 123);

        $roles = $this->auth->getRolesByUser('reader A');
        $this->assertInstanceOf(Role::class, reset($roles));
        $this->assertEquals($roles['reader']->name, 'reader');

        $roles = $this->auth->getRolesByUser(0);
        $this->assertInstanceOf(Role::class, reset($roles));
        $this->assertEquals($roles['reader']->name, 'reader');

        $roles = $this->auth->getRolesByUser(123);
        $this->assertInstanceOf(Role::class, reset($roles));
        $this->assertEquals($roles['reader']->name, 'reader');

        $this->assertContains('myDefaultRole', array_keys($roles));
    }

    public function testGetChildRoles()
    {
        $this->prepareData();

        $roles = $this->auth->getChildRoles('withoutChildren');
        $this->assertCount(1, $roles);
        $this->assertInstanceOf(Role::class, reset($roles));
        $this->assertSame(reset($roles)->name, 'withoutChildren');

        $roles = $this->auth->getChildRoles('reader');
        $this->assertCount(1, $roles);
        $this->assertInstanceOf(Role::class, reset($roles));
        $this->assertSame(reset($roles)->name, 'reader');

        $roles = $this->auth->getChildRoles('author');
        $this->assertCount(2, $roles);
        $this->assertArrayHasKey('author', $roles);
        $this->assertArrayHasKey('reader', $roles);

        $roles = $this->auth->getChildRoles('admin');
        $this->assertCount(3, $roles);
        $this->assertArrayHasKey('admin', $roles);
        $this->assertArrayHasKey('author', $roles);
        $this->assertArrayHasKey('reader', $roles);
    }

    public function testAssignMultipleRoles()
    {
        $this->prepareData();

        $reader = $this->auth->getRole('reader');
        $author = $this->auth->getRole('author');
        $this->auth->assign($reader, 'readingAuthor');
        $this->auth->assign($author, 'readingAuthor');

        $this->auth = $this->createManager();

        $roles = $this->auth->getRolesByUser('readingAuthor');
        $roleNames = [];
        foreach ($roles as $role) {
            $roleNames[] = $role->name;
        }

        $this->assertContains('reader', $roleNames, 'Roles should contain reader. Currently it has: ' . implode(', ', $roleNames));
        $this->assertContains('author', $roleNames, 'Roles should contain author. Currently it has: ' . implode(', ', $roleNames));
    }

    public function testAssignmentsToIntegerId()
    {
        $this->prepareData();

        $reader = $this->auth->getRole('reader');
        $author = $this->auth->getRole('author');
        $this->auth->assign($reader, 42);
        $this->auth->assign($author, 1337);
        $this->auth->assign($reader, 1337);

        $this->auth = $this->createManager();

        $this->assertCount(0, $this->auth->getAssignments(0));
        $this->assertCount(1, $this->auth->getAssignments(42));
        $this->assertCount(2, $this->auth->getAssignments(1337));
    }

    public function testGetAssignmentsByRole()
    {
        $this->prepareData();
        $reader = $this->auth->getRole('reader');
        $this->auth->assign($reader, 123);

        $this->auth = $this->createManager();

        $this->assertEquals([], $this->auth->getUserIdsByRole('nonexisting'));
        $this->assertEquals(['reader A', '123'], $this->auth->getUserIdsByRole('reader'), '', 0.0, 10, true);
        $this->assertEquals(['author B'], $this->auth->getUserIdsByRole('author'));
        $this->assertEquals(['admin C'], $this->auth->getUserIdsByRole('admin'));
    }

    public function testCanAddChild()
    {
        $this->prepareData();

        $author = $this->auth->createRole('author');
        $reader = $this->auth->createRole('reader');

        $this->assertTrue($this->auth->canAddChild($author, $reader));
        $this->assertFalse($this->auth->canAddChild($reader, $author));
    }


    public function testRemoveAllRules()
    {
        $this->prepareData();

        $this->auth->removeAllRules();

        $this->assertEmpty($this->auth->getRules());

        $this->assertNotEmpty($this->auth->getRoles());
        $this->assertNotEmpty($this->auth->getPermissions());
    }

    public function testRemoveAllRoles()
    {
        $this->prepareData();

        $this->auth->removeAllRoles();

        $this->assertEmpty($this->auth->getRoles());

        $this->assertNotEmpty($this->auth->getRules());
        $this->assertNotEmpty($this->auth->getPermissions());
    }

    public function testRemoveAllPermissions()
    {
        $this->prepareData();

        $this->auth->removeAllPermissions();

        $this->assertEmpty($this->auth->getPermissions());

        $this->assertNotEmpty($this->auth->getRules());
        $this->assertNotEmpty($this->auth->getRoles());
    }

    public function RBACItemsProvider()
    {
        return [
            [Item::TYPE_ROLE],
            [Item::TYPE_PERMISSION],
        ];
    }

    /**
     * @dataProvider RBACItemsProvider
     * @param mixed $RBACItemType
     */
    public function testAssignRule($RBACItemType)
    {
        $auth = $this->auth;
        $userId = 3;

        $auth->removeAll();
        $item = $this->createRBACItem($RBACItemType, 'Admin');
        $auth->add($item);
        $auth->assign($item, $userId);
        $this->assertTrue($auth->checkAccess($userId, 'Admin'));

        // with normal register rule
        $auth->removeAll();
        $rule = new ActionRule();
        $auth->add($rule);
        $item = $this->createRBACItem($RBACItemType, 'Reader');
        $item->ruleName = $rule->name;
        $auth->add($item);
        $auth->assign($item, $userId);
        $this->assertTrue($auth->checkAccess($userId, 'Reader', ['action' => 'read']));
        $this->assertFalse($auth->checkAccess($userId, 'Reader', ['action' => 'write']));

        // using rule class name
        $auth->removeAll();
        $item = $this->createRBACItem($RBACItemType, 'Reader');
        $item->ruleName = 'yiiunit\framework\rbac\ActionRule';
        $auth->add($item);
        $auth->assign($item, $userId);
        $this->assertTrue($auth->checkAccess($userId, 'Reader', ['action' => 'read']));
        $this->assertFalse($auth->checkAccess($userId, 'Reader', ['action' => 'write']));

        // using DI
        \Yii::$container->set('write_rule', ['__class' => 'yiiunit\framework\rbac\ActionRule', 'action' => 'write']);
        \Yii::$container->set('delete_rule', ['__class' => 'yiiunit\framework\rbac\ActionRule', 'action' => 'delete']);
        \Yii::$container->set('all_rule', ['__class' => 'yiiunit\framework\rbac\ActionRule', 'action' => 'all']);

        $item = $this->createRBACItem($RBACItemType, 'Writer');
        $item->ruleName = 'write_rule';
        $auth->add($item);
        $auth->assign($item, $userId);
        $this->assertTrue($auth->checkAccess($userId, 'Writer', ['action' => 'write']));
        $this->assertFalse($auth->checkAccess($userId, 'Writer', ['action' => 'update']));

        $item = $this->createRBACItem($RBACItemType, 'Deleter');
        $item->ruleName = 'delete_rule';
        $auth->add($item);
        $auth->assign($item, $userId);
        $this->assertTrue($auth->checkAccess($userId, 'Deleter', ['action' => 'delete']));
        $this->assertFalse($auth->checkAccess($userId, 'Deleter', ['action' => 'update']));

        $item = $this->createRBACItem($RBACItemType, 'Author');
        $item->ruleName = 'all_rule';
        $auth->add($item);
        $auth->assign($item, $userId);
        $this->assertTrue($auth->checkAccess($userId, 'Author', ['action' => 'update']));

        // update role and rule
        $item = $this->getRBACItem($RBACItemType, 'Reader');
        $item->name = 'AdminPost';
        $item->ruleName = 'all_rule';
        $auth->update('Reader', $item);
        $this->assertTrue($auth->checkAccess($userId, 'AdminPost', ['action' => 'print']));
    }

    /**
     * @dataProvider RBACItemsProvider
     * @param mixed $RBACItemType
     */
    public function testRevokeRule($RBACItemType)
    {
        $userId = 3;
        $auth = $this->auth;

        $auth->removeAll();
        $item = $this->createRBACItem($RBACItemType, 'Admin');
        $auth->add($item);
        $auth->assign($item, $userId);

        $this->assertTrue($auth->revoke($item, $userId));
        $this->assertFalse($auth->checkAccess($userId, 'Admin'));

        $auth->removeAll();
        $rule = new ActionRule();
        $auth->add($rule);
        $item = $this->createRBACItem($RBACItemType, 'Reader');
        $item->ruleName = $rule->name;
        $auth->add($item);
        $auth->assign($item, $userId);

        $this->assertTrue($auth->revoke($item, $userId));
        $this->assertFalse($auth->checkAccess($userId, 'Reader', ['action' => 'read']));
        $this->assertFalse($auth->checkAccess($userId, 'Reader', ['action' => 'write']));
    }

    /**
     * Create Role or Permission RBAC item.
     * @param int $RBACItemType
     * @param string $name
     * @return Permission|Role
     */
    private function createRBACItem($RBACItemType, $name)
    {
        if ($RBACItemType === Item::TYPE_ROLE) {
            return $this->auth->createRole($name);
        }
        if ($RBACItemType === Item::TYPE_PERMISSION) {
            return $this->auth->createPermission($name);
        }

        throw new \InvalidArgumentException();
    }

    /**
     * Get Role or Permission RBAC item.
     * @param int $RBACItemType
     * @param string $name
     * @return Permission|Role
     */
    private function getRBACItem($RBACItemType, $name)
    {
        if ($RBACItemType === Item::TYPE_ROLE) {
            return $this->auth->getRole($name);
        }
        if ($RBACItemType === Item::TYPE_PERMISSION) {
            return $this->auth->getPermission($name);
        }

        throw new \InvalidArgumentException();
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/10176
     * @see https://github.com/yiisoft/yii2/issues/12681
     */
    public function testRuleWithPrivateFields()
    {
        $auth = $this->auth;

        $auth->removeAll();

        $rule = new ActionRule();
        $auth->add($rule);

        /** @var ActionRule $rule */
        $rule = $this->auth->getRule('action_rule');
        $this->assertInstanceOf(ActionRule::class, $rule);
    }

    public function testDefaultRolesWithClosureReturningNonArrayValue()
    {
        $this->expectException('yii\base\InvalidValueException');
        $this->expectExceptionMessage('Default roles closure must return an array');
        $this->auth->defaultRoles = function () {
            return 'test';
        };
    }

    public function testDefaultRolesWithNonArrayValue()
    {
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage('Default roles must be either an array or a callable');
        $this->auth->defaultRoles = 'test';
    }
}
