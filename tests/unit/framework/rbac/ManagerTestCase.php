<?php

namespace yiiunit\framework\rbac;

use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\PhpManager;
use yii\rbac\Role;
use yiiunit\TestCase;

/**
 * ManagerTestCase
 */
abstract class ManagerTestCase extends TestCase
{
    /**
     * @var \yii\rbac\ManagerInterface
     */
    protected $auth;

    /**
     * @return \yii\rbac\ManagerInterface
     */
    abstract protected function createManager();

    public function testCreateRole()
    {
        $role = $this->auth->createRole('admin');
        $this->assertTrue($role instanceof Role);
        $this->assertEquals(Item::TYPE_ROLE, $role->type);
        $this->assertEquals('admin', $role->name);
    }

    public function testCreatePermission()
    {
        $permission = $this->auth->createPermission('edit post');
        $this->assertTrue($permission instanceof Permission);
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
        $this->assertEquals(true, $rule->reallyReally);
    }

    public function testUpdateRule()
    {
        $this->prepareData();

        $rule = $this->auth->getRule('isAuthor');
        $rule->name = "newName";
        $rule->reallyReally = false;
        $this->auth->update('isAuthor', $rule);

        $rule = $this->auth->getRule('isAuthor');
        $this->assertEquals(null, $rule);

        $rule = $this->auth->getRule('newName');
        $this->assertEquals("newName", $rule->name);
        $this->assertEquals(false, $rule->reallyReally);

        $rule->reallyReally = true;
        $this->auth->update('newName', $rule);

        $rule = $this->auth->getRule('newName');
        $this->assertEquals(true, $rule->reallyReally);
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
                'updateAnyPost' => false,
            ],
            'admin C' => [
                'createPost' => true,
                'readPost' => true,
                'updatePost' => false,
                'updateAnyPost' => true,
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
        $rule = new AuthorRule;
        $this->auth->add($rule);

        $createPost = $this->auth->createPermission('createPost');
        $createPost->description = 'create a post';
        $this->auth->add($createPost);

        $readPost = $this->auth->createPermission('readPost');
        $readPost->description = 'read a post';
        $this->auth->add($readPost);

        $updatePost = $this->auth->createPermission('updatePost');
        $updatePost->description = 'update a post';
        $updatePost->ruleName = $rule->name;
        $this->auth->add($updatePost);

        $updateAnyPost = $this->auth->createPermission('updateAnyPost');
        $updateAnyPost->description = 'update any post';
        $this->auth->add($updateAnyPost);

        $reader = $this->auth->createRole('reader');
        $this->auth->add($reader);
        $this->auth->addChild($reader, $readPost);

        $author = $this->auth->createRole('author');
        $this->auth->add($author);
        $this->auth->addChild($author, $createPost);
        $this->auth->addChild($author, $updatePost);
        $this->auth->addChild($author, $reader);

        $admin = $this->auth->createRole('admin');
        $this->auth->add($admin);
        $this->auth->addChild($admin, $author);
        $this->auth->addChild($admin, $updateAnyPost);

        $this->auth->assign($reader, 'reader A');
        $this->auth->assign($author, 'author B');
        $this->auth->assign($admin, 'admin C');
    }

    public function testGetPermissionsByRole()
    {
        $this->prepareData();
        $roles = $this->auth->getPermissionsByRole('admin');
        $expectedPermissions = ['createPost', 'updatePost', 'readPost', 'updateAnyPost'];
        $this->assertEquals(count($roles), count($expectedPermissions));
        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($roles[$permission] instanceof Permission);
        }
    }

    public function testGetPermissionsByUser()
    {
        $this->prepareData();
        $roles = $this->auth->getPermissionsByUser('author B');
        $expectedPermissions = ['createPost', 'updatePost', 'readPost'];
        $this->assertEquals(count($roles), count($expectedPermissions));
        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($roles[$permission] instanceof Permission);
        }
    }

    public function testGetRolesByUser()
    {
        $this->prepareData();
        $roles = $this->auth->getRolesByUser('reader A');
        $this->assertTrue(reset($roles) instanceof Role);
        $this->assertEquals($roles['reader']->name, 'reader');
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

        $this->assertEquals(0, count($this->auth->getAssignments(0)));
        $this->assertEquals(1, count($this->auth->getAssignments(42)));
        $this->assertEquals(2, count($this->auth->getAssignments(1337)));
    }
}
