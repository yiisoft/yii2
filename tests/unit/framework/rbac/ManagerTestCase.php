<?php

namespace yiiunit\framework\rbac;

use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;
use yiiunit\TestCase;

abstract class ManagerTestCase extends TestCase
{
    /**
     * @var \yii\rbac\ManagerInterface
     */
    protected $auth;

    public function testCreateRoleAndPermission()
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
/*
    public function testRemove()
    {

    }

    public function testUpdate()
    {

    }

    public function testCreateItem()
    {
        $type = Item::TYPE_TASK;
        $name = 'editUser';
        $description = 'edit a user';
        $ruleName = 'isAuthor';
        $data = [1, 2, 3];
        $item = $this->auth->createItem($name, $type, $description, $ruleName, $data);
        $this->assertTrue($item instanceof Item);
        $this->assertEquals($item->type, $type);
        $this->assertEquals($item->name, $name);
        $this->assertEquals($item->description, $description);
        $this->assertEquals($item->ruleName, $ruleName);
        $this->assertEquals($item->data, $data);

        // test shortcut
        $name2 = 'createUser';
        $item2 = $this->auth->createRole($name2, $description, $ruleName, $data);
        $this->assertEquals($item2->type, Item::TYPE_ROLE);

        // test adding an item with the same name
        $this->setExpectedException('\yii\base\Exception');
        $this->auth->createItem($name, $type, $description, $ruleName, $data);
    }

    public function testGetItem()
    {
        $this->assertTrue($this->auth->getItem('readPost') instanceof Item);
        $this->assertTrue($this->auth->getItem('reader') instanceof Item);
        $this->assertNull($this->auth->getItem('unknown'));
    }

    public function testRemoveItem()
    {
        $this->assertTrue($this->auth->getItem('updatePost') instanceof Item);
        $this->assertTrue($this->auth->removeItem('updatePost'));
        $this->assertNull($this->auth->getItem('updatePost'));
        $this->assertFalse($this->auth->removeItem('updatePost'));
    }

    public function testChangeItemName()
    {
        $item = $this->auth->getItem('readPost');
        $this->assertTrue($item instanceof Item);
        $this->assertTrue($this->auth->hasItemChild('reader', 'readPost'));
        $item->name = 'readPost2';
        $item->save();
        $this->assertNull($this->auth->getItem('readPost'));
        $this->assertEquals($this->auth->getItem('readPost2'), $item);
        $this->assertFalse($this->auth->hasItemChild('reader', 'readPost'));
        $this->assertTrue($this->auth->hasItemChild('reader', 'readPost2'));
    }

    public function testAddItemChild()
    {
        $this->auth->addItemChild('createPost', 'updatePost');

        // test adding upper level item to lower one
        $this->setExpectedException('\yii\base\Exception');
        $this->auth->addItemChild('readPost', 'reader');
    }

    public function testAddItemChild2()
    {
        // test adding inexistent items
        $this->setExpectedException('\yii\base\Exception');
        $this->assertFalse($this->auth->addItemChild('createPost2', 'updatePost'));
    }

    public function testRemoveItemChild()
    {
        $this->assertTrue($this->auth->hasItemChild('reader', 'readPost'));
        $this->assertTrue($this->auth->removeItemChild('reader', 'readPost'));
        $this->assertFalse($this->auth->hasItemChild('reader', 'readPost'));
        $this->assertFalse($this->auth->removeItemChild('reader', 'readPost'));
    }

    public function testGetItemChildren()
    {
        $this->assertEquals([], $this->auth->getItemChildren('readPost'));
        $children = $this->auth->getItemChildren('author');
        $this->assertEquals(3, count($children));
        $this->assertTrue(reset($children) instanceof Item);
    }

    public function testAssign()
    {
        $auth = $this->auth->assign('new user', 'createPost', 'isAuthor', 'data');
        $this->assertTrue($auth instanceof Assignment);
        $this->assertEquals($auth->userId, 'new user');
        $this->assertEquals($auth->itemName, 'createPost');
        $this->assertEquals($auth->ruleName, 'isAuthor');
        $this->assertEquals($auth->data, 'data');

        $this->setExpectedException('\yii\base\Exception');
        $this->auth->assign('new user', 'createPost2', 'rule', 'data');
    }

    public function testRevoke()
    {
        $this->assertTrue($this->auth->isAssigned('author B', 'author'));
        $auth = $this->auth->getAssignment('author B', 'author');
        $this->assertTrue($auth instanceof Assignment);
        $this->assertTrue($this->auth->revoke('author B', 'author'));
        $this->assertFalse($this->auth->isAssigned('author B', 'author'));
        $this->assertFalse($this->auth->revoke('author B', 'author'));
    }

    public function testRevokeAll()
    {
        $this->assertTrue($this->auth->revokeAll('reader E'));
        $this->assertFalse($this->auth->isAssigned('reader E', 'reader'));
    }

    public function testGetAssignments()
    {
        $this->auth->assign('author B', 'deletePost');
        $auths = $this->auth->getAssignments('author B');
        $this->assertEquals(2, count($auths));
        $this->assertTrue(reset($auths) instanceof Assignment);
    }

    public function testGetItems()
    {
        $this->assertEquals(count($this->auth->getRoles()), 4);
        $this->assertEquals(count($this->auth->getOperations()), 4);
        $this->assertEquals(count($this->auth->getTasks()), 1);
        $this->assertEquals(count($this->auth->getItems()), 9);

        $this->assertEquals(count($this->auth->getItems('author B', null)), 1);
        $this->assertEquals(count($this->auth->getItems('author C', null)), 0);
        $this->assertEquals(count($this->auth->getItems('author B', Item::TYPE_ROLE)), 1);
        $this->assertEquals(count($this->auth->getItems('author B', Item::TYPE_OPERATION)), 0);
    }

    public function testClearAll()
    {
        $this->auth->clearAll();
        $this->assertEquals(count($this->auth->getRoles()), 0);
        $this->assertEquals(count($this->auth->getOperations()), 0);
        $this->assertEquals(count($this->auth->getTasks()), 0);
        $this->assertEquals(count($this->auth->getItems()), 0);
        $this->assertEquals(count($this->auth->getAssignments('author B')), 0);
    }

    public function testClearAssignments()
    {
        $this->auth->clearAssignments();
        $this->assertEquals(count($this->auth->getAssignments('author B')), 0);
    }

    public function testDetectLoop()
    {
        $this->setExpectedException('\yii\base\Exception');
        $this->auth->addItemChild('readPost', 'readPost');
    }
    */

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
}
