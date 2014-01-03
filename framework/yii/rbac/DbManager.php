<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\db\Expression;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;

/**
 * DbManager represents an authorization manager that stores authorization information in database.
 *
 * The database connection is specified by [[db]]. And the database schema
 * should be as described in "framework/rbac/*.sql". You may change the names of
 * the three tables used to store the authorization data by setting [[itemTable]],
 * [[itemChildTable]] and [[assignmentTable]].
 *
 * @property Item[] $items The authorization items of the specific type. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class DbManager extends Manager
{
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the DbManager object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 */
	public $db = 'db';
	/**
	 * @var string the name of the table storing authorization items. Defaults to 'tbl_auth_item'.
	 */
	public $itemTable = 'tbl_auth_item';
	/**
	 * @var string the name of the table storing authorization item hierarchy. Defaults to 'tbl_auth_item_child'.
	 */
	public $itemChildTable = 'tbl_auth_item_child';
	/**
	 * @var string the name of the table storing authorization item assignments. Defaults to 'tbl_auth_assignment'.
	 */
	public $assignmentTable = 'tbl_auth_assignment';

	private $_usingSqlite;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by establishing the database connection.
	 */
	public function init()
	{
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException("DbManager::db must be either a DB connection instance or the application component ID of a DB connection.");
		}
		$this->_usingSqlite = !strncmp($this->db->getDriverName(), 'sqlite', 6);
		parent::init();
	}

	/**
	 * Performs access check for the specified user.
	 * @param mixed $userId the user ID. This should can be either an integer or a string representing
	 * the unique identifier of a user. See [[User::id]].
	 * @param string $itemName the name of the operation that need access check
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user. A param with name 'userId' is added to this array,
	 * which holds the value of `$userId`.
	 * @return boolean whether the operations can be performed by the user.
	 */
	public function checkAccess($userId, $itemName, $params = [])
	{
		$assignments = $this->getAssignments($userId);
		return $this->checkAccessRecursive($userId, $itemName, $params, $assignments);
	}

	/**
	 * Performs access check for the specified user.
	 * This method is internally called by [[checkAccess()]].
	 * @param mixed $userId the user ID. This should can be either an integer or a string representing
	 * the unique identifier of a user. See [[User::id]].
	 * @param string $itemName the name of the operation that need access check
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user. A param with name 'userId' is added to this array,
	 * which holds the value of `$userId`.
	 * @param Assignment[] $assignments the assignments to the specified user
	 * @return boolean whether the operations can be performed by the user.
	 */
	protected function checkAccessRecursive($userId, $itemName, $params, $assignments)
	{
		if (($item = $this->getItem($itemName)) === null) {
			return false;
		}
		Yii::trace('Checking permission: ' . $item->getName(), __METHOD__);
		if (!isset($params['userId'])) {
			$params['userId'] = $userId;
		}
		if ($this->executeBizRule($item->bizRule, $params, $item->data)) {
			if (in_array($itemName, $this->defaultRoles)) {
				return true;
			}
			if (isset($assignments[$itemName])) {
				$assignment = $assignments[$itemName];
				if ($this->executeBizRule($assignment->bizRule, $params, $assignment->data)) {
					return true;
				}
			}
			$query = new Query;
			$parents = $query->select(['parent'])
							 ->from($this->itemChildTable)
							 ->where(['child' => $itemName])
							 ->createCommand($this->db)
							 ->queryColumn();
			foreach ($parents as $parent) {
				if ($this->checkAccessRecursive($userId, $parent, $params, $assignments)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Adds an item as a child of another item.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the item is added successfully
	 * @throws Exception if either parent or child doesn't exist.
	 * @throws InvalidCallException if a loop has been detected.
	 */
	public function addItemChild($itemName, $childName)
	{
		if ($itemName === $childName) {
			throw new Exception("Cannot add '$itemName' as a child of itself.");
		}
		$query = new Query;
		$rows = $query->from($this->itemTable)
					  ->where(['or', 'name=:name1', 'name=:name2'], [':name1' => $itemName, ':name2' => $childName])
					  ->createCommand($this->db)
					  ->queryAll();
		if (count($rows) == 2) {
			if ($rows[0]['name'] === $itemName) {
				$parentType = $rows[0]['type'];
				$childType = $rows[1]['type'];
			} else {
				$childType = $rows[0]['type'];
				$parentType = $rows[1]['type'];
			}
			$this->checkItemChildType($parentType, $childType);
			if ($this->detectLoop($itemName, $childName)) {
				throw new InvalidCallException("Cannot add '$childName' as a child of '$itemName'. A loop has been detected.");
			}
			$this->db->createCommand()
					 ->insert($this->itemChildTable, ['parent' => $itemName, 'child' => $childName])
					 ->execute();
			return true;
		} else {
			throw new Exception("Either '$itemName' or '$childName' does not exist.");
		}
	}

	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the removal is successful
	 */
	public function removeItemChild($itemName, $childName)
	{
		return $this->db->createCommand()
						->delete($this->itemChildTable, ['parent' => $itemName, 'child' => $childName])
						->execute() > 0;
	}

	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	public function hasItemChild($itemName, $childName)
	{
		$query = new Query;
		return $query->select(['parent'])
					 ->from($this->itemChildTable)
					 ->where(['parent' => $itemName, 'child' => $childName])
					 ->createCommand($this->db)
					 ->queryScalar() !== false;
	}

	/**
	 * Returns the children of the specified item.
	 * @param mixed $names the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names.
	 * @return Item[] all child items of the parent
	 */
	public function getItemChildren($names)
	{
		$query = new Query;
		$rows = $query->select(['name', 'type', 'description', 'biz_rule', 'data'])
					  ->from([$this->itemTable, $this->itemChildTable])
					  ->where(['parent' => $names, 'name' => new Expression('child')])
					  ->createCommand($this->db)
					  ->queryAll();
		$children = [];
		foreach ($rows as $row) {
			if (($data = @unserialize($row['data'])) === false) {
				$data = null;
			}
			$children[$row['name']] = new Item([
				'manager' => $this,
				'name' => $row['name'],
				'type' => $row['type'],
				'description' => $row['description'],
				'bizRule' => $row['biz_rule'],
				'data' => $data,
			]);
		}
		return $children;
	}

	/**
	 * Assigns an authorization item to a user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @param string $bizRule the business rule to be executed when [[checkAccess()]] is called
	 * for this particular authorization item.
	 * @param mixed $data additional data associated with this assignment
	 * @return Assignment the authorization assignment information.
	 * @throws InvalidParamException if the item does not exist or if the item has already been assigned to the user
	 */
	public function assign($userId, $itemName, $bizRule = null, $data = null)
	{
		if ($this->usingSqlite() && $this->getItem($itemName) === null) {
			throw new InvalidParamException("The item '$itemName' does not exist.");
		}
		$this->db->createCommand()
				 ->insert($this->assignmentTable, [
					 'user_id' => $userId,
					 'item_name' => $itemName,
					 'biz_rule' => $bizRule,
					 'data' => serialize($data),
				 ])
				 ->execute();
		return new Assignment([
			'manager' => $this,
			'userId' => $userId,
			'itemName' => $itemName,
			'bizRule' => $bizRule,
			'data' => $data,
		]);
	}

	/**
	 * Revokes an authorization assignment from a user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return boolean whether removal is successful
	 */
	public function revoke($userId, $itemName)
	{
		return $this->db->createCommand()
						->delete($this->assignmentTable, ['user_id' => $userId, 'item_name' => $itemName])
						->execute() > 0;
	}

	/**
	 * Revokes all authorization assignments from a user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @return boolean whether removal is successful
	 */
	public function revokeAll($userId)
	{
		return $this->db->createCommand()
						->delete($this->assignmentTable, ['user_id' => $userId])
						->execute() > 0;
	}

	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return boolean whether the item has been assigned to the user.
	 */
	public function isAssigned($userId, $itemName)
	{
		$query = new Query;
		return $query->select(['item_name'])
					 ->from($this->assignmentTable)
					 ->where(['user_id' => $userId, 'item_name' => $itemName])
					 ->createCommand($this->db)
					 ->queryScalar() !== false;
	}

	/**
	 * Returns the item assignment information.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return Assignment the item assignment information. Null is returned if
	 * the item is not assigned to the user.
	 */
	public function getAssignment($userId, $itemName)
	{
		$query = new Query;
		$row = $query->from($this->assignmentTable)
					 ->where(['user_id' => $userId, 'item_name' => $itemName])
					 ->createCommand($this->db)
					 ->queryOne();
		if ($row !== false) {
			if (($data = @unserialize($row['data'])) === false) {
				$data = null;
			}
			return new Assignment([
				'manager' => $this,
				'userId' => $row['user_id'],
				'itemName' => $row['item_name'],
				'bizRule' => $row['biz_rule'],
				'data' => $data,
			]);
		} else {
			return null;
		}
	}

	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @return Assignment[] the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	public function getAssignments($userId)
	{
		$query = new Query;
		$rows = $query->from($this->assignmentTable)
					  ->where(['user_id' => $userId])
					  ->createCommand($this->db)
					  ->queryAll();
		$assignments = [];
		foreach ($rows as $row) {
			if (($data = @unserialize($row['data'])) === false) {
				$data = null;
			}
			$assignments[$row['item_name']] = new Assignment([
				'manager' => $this,
				'userId' => $row['user_id'],
				'itemName' => $row['item_name'],
				'bizRule' => $row['biz_rule'],
				'data' => $data,
			]);
		}
		return $assignments;
	}

	/**
	 * Saves the changes to an authorization assignment.
	 * @param Assignment $assignment the assignment that has been changed.
	 */
	public function saveAssignment($assignment)
	{
		$this->db->createCommand()
				 ->update($this->assignmentTable, [
					 'biz_rule' => $assignment->bizRule,
					 'data' => serialize($assignment->data),
				 ], [
					 'user_id' => $assignment->userId,
					 'item_name' => $assignment->itemName,
				 ])
				 ->execute();
	}

	/**
	 * Returns the authorization items of the specific type and user.
	 * @param mixed $userId the user ID. Defaults to null, meaning returning all items even if
	 * they are not assigned to a user.
	 * @param integer $type the item type (0: operation, 1: task, 2: role). Defaults to null,
	 * meaning returning all items regardless of their type.
	 * @return Item[] the authorization items of the specific type.
	 */
	public function getItems($userId = null, $type = null)
	{
		$query = new Query;
		if ($userId === null && $type === null) {
			$command = $query->from($this->itemTable)
							 ->createCommand($this->db);
		} elseif ($userId === null) {
			$command = $query->from($this->itemTable)
							 ->where(['type' => $type])
							 ->createCommand($this->db);
		} elseif ($type === null) {
			$command = $query->select(['name', 'type', 'description', 't1.biz_rule', 't1.data'])
							 ->from([$this->itemTable . ' t1', $this->assignmentTable . ' t2'])
							 ->where(['user_id' => $userId, 'name' => new Expression('item_name')])
							 ->createCommand($this->db);
		} else {
			$command = $query->select(['name', 'type', 'description', 't1.biz_rule', 't1.data'])
							 ->from([$this->itemTable . ' t1', $this->assignmentTable . ' t2'])
							 ->where(['user_id' => $userId, 'type' => $type, 'name' => new Expression('item_name')])
							 ->createCommand($this->db);
		}
		$items = [];
		foreach ($command->queryAll() as $row) {
			if (($data = @unserialize($row['data'])) === false) {
				$data = null;
			}
			$items[$row['name']] = new Item([
				'manager' => $this,
				'name' => $row['name'],
				'type' => $row['type'],
				'description' => $row['description'],
				'bizRule' => $row['biz_rule'],
				'data' => $data,
			]);
		}
		return $items;
	}

	/**
	 * Creates an authorization item.
	 * An authorization item represents an action permission (e.g. creating a post).
	 * It has three types: operation, task and role.
	 * Authorization items form a hierarchy. Higher level items inheirt permissions representing
	 * by lower level items.
	 * @param string $name the item name. This must be a unique identifier.
	 * @param integer $type the item type (0: operation, 1: task, 2: role).
	 * @param string $description description of the item
	 * @param string $bizRule business rule associated with the item. This is a piece of
	 * PHP code that will be executed when [[checkAccess()]] is called for the item.
	 * @param mixed $data additional data associated with the item.
	 * @return Item the authorization item
	 * @throws Exception if an item with the same name already exists
	 */
	public function createItem($name, $type, $description = '', $bizRule = null, $data = null)
	{
		$this->db->createCommand()
				 ->insert($this->itemTable, [
					 'name' => $name,
					 'type' => $type,
					 'description' => $description,
					 'biz_rule' => $bizRule,
					 'data' => serialize($data),
				 ])
				 ->execute();
		return new Item([
			'manager' => $this,
			'name' => $name,
			'type' => $type,
			'description' => $description,
			'bizRule' => $bizRule,
			'data' => $data,
		]);
	}

	/**
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	public function removeItem($name)
	{
		if ($this->usingSqlite()) {
			$this->db->createCommand()
					 ->delete($this->itemChildTable, ['or', 'parent=:name', 'child=:name'], [':name' => $name])
					 ->execute();
			$this->db->createCommand()
					 ->delete($this->assignmentTable, ['item_name' => $name])
					 ->execute();
		}
		return $this->db->createCommand()
						->delete($this->itemTable, ['name' => $name])
						->execute() > 0;
	}

	/**
	 * Returns the authorization item with the specified name.
	 * @param string $name the name of the item
	 * @return Item the authorization item. Null if the item cannot be found.
	 */
	public function getItem($name)
	{
		$query = new Query;
		$row = $query->from($this->itemTable)
					 ->where(['name' => $name])
					 ->createCommand($this->db)
					 ->queryOne();

		if ($row !== false) {
			if (($data = @unserialize($row['data'])) === false) {
				$data = null;
			}
			return new Item([
				'manager' => $this,
				'name' => $row['name'],
				'type' => $row['type'],
				'description' => $row['description'],
				'bizRule' => $row['biz_rule'],
				'data' => $data,
			]);
		} else {
			return null;
		}
	}

	/**
	 * Saves an authorization item to persistent storage.
	 * @param Item $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 */
	public function saveItem($item, $oldName = null)
	{
		if ($this->usingSqlite() && $oldName !== null && $item->getName() !== $oldName) {
			$this->db->createCommand()
					 ->update($this->itemChildTable, ['parent' => $item->getName()], ['parent' => $oldName])
					 ->execute();
			$this->db->createCommand()
					 ->update($this->itemChildTable, ['child' => $item->getName()], ['child' => $oldName])
					 ->execute();
			$this->db->createCommand()
					 ->update($this->assignmentTable, ['item_name' => $item->getName()], ['item_name' => $oldName])
					 ->execute();
		}

		$this->db->createCommand()
				 ->update($this->itemTable, [
					 'name' => $item->getName(),
					 'type' => $item->type,
					 'description' => $item->description,
					 'biz_rule' => $item->bizRule,
					 'data' => serialize($item->data),
				 ], [
					 'name' => $oldName === null ? $item->getName() : $oldName,
				 ])
				 ->execute();
	}

	/**
	 * Saves the authorization data to persistent storage.
	 */
	public function save()
	{
	}

	/**
	 * Removes all authorization data.
	 */
	public function clearAll()
	{
		$this->clearAssignments();
		$this->db->createCommand()->delete($this->itemChildTable)->execute();
		$this->db->createCommand()->delete($this->itemTable)->execute();
	}

	/**
	 * Removes all authorization assignments.
	 */
	public function clearAssignments()
	{
		$this->db->createCommand()->delete($this->assignmentTable)->execute();
	}

	/**
	 * Checks whether there is a loop in the authorization item hierarchy.
	 * @param string $itemName parent item name
	 * @param string $childName the name of the child item that is to be added to the hierarchy
	 * @return boolean whether a loop exists
	 */
	protected function detectLoop($itemName, $childName)
	{
		if ($childName === $itemName) {
			return true;
		}
		foreach ($this->getItemChildren($childName) as $child) {
			if ($this->detectLoop($itemName, $child->getName())) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return boolean whether the database is a SQLite database
	 */
	protected function usingSqlite()
	{
		return $this->_usingSqlite;
	}
}
