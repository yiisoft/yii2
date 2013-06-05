<?php

namespace yiiunit\framework\rbac;

use yii\rbac\Assignment;
use yii\rbac\Item;
use yiiunit\TestCase;

abstract class ManagerTestCase extends TestCase
{
	/** @var \yii\rbac\PhpManager|\yii\rbac\DbManager */
	protected $auth;

	public function testCreateItem()
	{
		$type = Item::TYPE_TASK;
		$name = 'editUser';
		$description = 'edit a user';
		$bizRule = 'checkUserIdentity()';
		$data = array(1, 2, 3);
		$item = $this->auth->createItem($name, $type, $description, $bizRule, $data);
		$this->assertTrue($item instanceof Item);
		$this->assertEquals($item->type, $type);
		$this->assertEquals($item->name, $name);
		$this->assertEquals($item->description, $description);
		$this->assertEquals($item->bizRule, $bizRule);
		$this->assertEquals($item->data, $data);

		// test shortcut
		$name2 = 'createUser';
		$item2 = $this->auth->createRole($name2, $description, $bizRule, $data);
		$this->assertEquals($item2->type, Item::TYPE_ROLE);

		// test adding an item with the same name
		$this->setExpectedException('\yii\base\Exception');
		$this->auth->createItem($name, $type, $description, $bizRule, $data);
	}

	public function testGetItem()
	{
		$this->assertTrue($this->auth->getItem('readPost') instanceof Item);
		$this->assertTrue($this->auth->getItem('reader') instanceof Item);
		$this->assertNull($this->auth->getItem('unknown'));
	}

	public function testRemoveAuthItem()
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
		$this->assertEquals(array(), $this->auth->getItemChildren('readPost'));
		$children = $this->auth->getItemChildren('author');
		$this->assertEquals(3, count($children));
		$this->assertTrue(reset($children) instanceof Item);
	}

	public function testAssign()
	{
		$auth = $this->auth->assign('new user', 'createPost', 'rule', 'data');
		$this->assertTrue($auth instanceof Assignment);
		$this->assertEquals($auth->userId, 'new user');
		$this->assertEquals($auth->itemName, 'createPost');
		$this->assertEquals($auth->bizRule, 'rule');
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

	public static function bizEval($manager,$params,$data)
	{
		if (is_array($data) && array_key_exists('bizRule',$data)) {
			$bizRule = $data['bizRule'];
			unset($data['bizRule']);
		} else {
			$bizRule = null;
		}
					
		return $bizRule === '' || $bizRule === null || ($manager->showErrors ? eval($bizRule) != 0 : @eval($bizRule) != 0);
	}
	
	public function testExecuteBizRule()
	{
		
		$this->assertTrue($this->auth->executeBizRule(null, array(), null));
		
		$this->assertTrue($this->auth->executeBizRule(function() {return true;}, array(), null));
		$this->assertFalse($this->auth->executeBizRule(function() {return false;}, array(), null));
		$this->assertTrue($this->auth->executeBizRule(function($manager,$params,$data) {return $params[0] == $params[1];}, array(1, '1'), null));
		$this->assertTrue($this->auth->executeBizRule(function($manager,$params,$data) {return is_string($data);}, array(), 'test'));
		
		// eval style biz rules shouldn't work any longer. Ensure they return false
		$this->assertFalse($this->auth->executeBizRule('return 1 == true;', array(), null));
		$this->assertFalse($this->auth->executeBizRule('return $params[0] == $params[1];', array(1, '1'), null));
		$this->assertFalse($this->auth->executeBizRule('invalid;', array(), null));
		
		// simulating eval style biz rules using custom evaluating biz rule
		$callback = array('\yiiunit\framework\rbac\ManagerTestCase','bizEval');
		$this->assertTrue($this->auth->executeBizRule($callback, array(), array('bizRule'=>'return 1 == true;')));
		$this->assertTrue($this->auth->executeBizRule($callback, array(1,'1'), array('bizRule'=>'return $params[0] == $params[1];')));
		$this->assertFalse($this->auth->executeBizRule($callback, array(), array('bizRule'=>'invalid;')));
		
	}

	public function testCheckAccess()
	{
		$results = array(
			'reader A' => array(
				'createPost' => false,
				'readPost' => true,
				'updatePost' => false,
				'updateOwnPost' => false,
				'deletePost' => false,
			),
			'author B' => array(
				'createPost' => true,
				'readPost' => true,
				'updatePost' => true,
				'updateOwnPost' => true,
				'deletePost' => false,
			),
			'editor C' => array(
				'createPost' => false,
				'readPost' => true,
				'updatePost' => true,
				'updateOwnPost' => false,
				'deletePost' => false,
			),
			'admin D' => array(
				'createPost' => true,
				'readPost' => true,
				'updatePost' => true,
				'updateOwnPost' => false,
				'deletePost' => true,
			),
		);

		$params = array('authorID' => 'author B');

		foreach (array('reader A', 'author B', 'editor C', 'admin D') as $user) {
			$params['userID'] = $user;
			foreach (array('createPost', 'readPost', 'updatePost', 'updateOwnPost', 'deletePost') as $operation) {
				$result = $this->auth->checkAccess($user, $operation, $params);
				$this->assertEquals($results[$user][$operation], $result);
			}
		}
	}

	public static function bizUpdateOwnPost($manager,$params,$data)
	{
		return $params["authorID"] == $params["userID"];
	}
	
	protected function prepareData()
	{
		$this->auth->createOperation('createPost', 'create a post');
		$this->auth->createOperation('readPost', 'read a post');
		$this->auth->createOperation('updatePost', 'update a post');
		$this->auth->createOperation('deletePost', 'delete a post');

		$task = $this->auth->createTask(
			'updateOwnPost',
			'update a post by author himself',
			array('\yiiunit\framework\rbac\ManagerTestCase','bizUpdateOwnPost'));
		$task->addChild('updatePost');

		$role = $this->auth->createRole('reader');
		$role->addChild('readPost');

		$role = $this->auth->createRole('author');
		$role->addChild('reader');
		$role->addChild('createPost');
		$role->addChild('updateOwnPost');

		$role = $this->auth->createRole('editor');
		$role->addChild('reader');
		$role->addChild('updatePost');

		$role = $this->auth->createRole('admin');
		$role->addChild('editor');
		$role->addChild('author');
		$role->addChild('deletePost');

		$this->auth->assign('reader A', 'reader');
		$this->auth->assign('author B', 'author');
		$this->auth->assign('editor C', 'editor');
		$this->auth->assign('admin D', 'admin');
	}
}
