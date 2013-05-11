<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

/**
 * Manager is the base class for authorization manager classes.
 *
 * Manager extends [[Component]] and implements some methods
 * that are common among authorization manager classes.
 *
 * Manager together with its concrete child classes implement the Role-Based
 * Access Control (RBAC).
 *
 * The main idea is that permissions are organized as a hierarchy of
 * [[Item]] authorization items. Items on higer level inherit the permissions
 * represented by items on lower level. And roles are simply top-level authorization items
 * that may be assigned to individual users. A user is said to have a permission
 * to do something if the corresponding authorization item is inherited by one of his roles.
 *
 * Using authorization manager consists of two aspects. First, the authorization hierarchy
 * and assignments have to be established. Manager and its child classes
 * provides APIs to accomplish this task. Developers may need to develop some GUI
 * so that it is more intuitive to end-users. Second, developers call [[Manager::checkAccess()]]
 * at appropriate places in the application code to check if the current user
 * has the needed permission for an operation.
 *
 * @property array $roles Roles (name => Item).
 * @property array $tasks Tasks (name => Item).
 * @property array $operations Operations (name => Item).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
abstract class Manager extends Component
{
	/**
	 * @var boolean Enable error reporting for bizRules.
	 */
	public $showErrors = false;

	/**
	 * @var array list of role names that are assigned to all users implicitly.
	 * These roles do not need to be explicitly assigned to any user.
	 * When calling [[checkAccess()]], these roles will be checked first.
	 * For performance reason, you should minimize the number of such roles.
	 * A typical usage of such roles is to define an 'authenticated' role and associate
	 * it with a biz rule which checks if the current user is authenticated.
	 * And then declare 'authenticated' in this property so that it can be applied to
	 * every authenticated user.
	 */
	public $defaultRoles = array();

	/**
	 * Creates a role.
	 * This is a shortcut method to [[Manager::createItem()]].
	 * @param string $name the item name
	 * @param string $description the item description.
	 * @param string $bizRule the business rule associated with this item
	 * @param mixed $data additional data to be passed when evaluating the business rule
	 * @return Item the authorization item
	 */
	public function createRole($name, $description = '', $bizRule = null, $data = null)
	{
		return $this->createItem($name, Item::TYPE_ROLE, $description, $bizRule, $data);
	}

	/**
	 * Creates a task.
	 * This is a shortcut method to [[Manager::createItem()]].
	 * @param string $name the item name
	 * @param string $description the item description.
	 * @param string $bizRule the business rule associated with this item
	 * @param mixed $data additional data to be passed when evaluating the business rule
	 * @return Item the authorization item
	 */
	public function createTask($name, $description = '', $bizRule = null, $data = null)
	{
		return $this->createItem($name, Item::TYPE_TASK, $description, $bizRule, $data);
	}

	/**
	 * Creates an operation.
	 * This is a shortcut method to [[Manager::createItem()]].
	 * @param string $name the item name
	 * @param string $description the item description.
	 * @param string $bizRule the business rule associated with this item
	 * @param mixed $data additional data to be passed when evaluating the business rule
	 * @return Item the authorization item
	 */
	public function createOperation($name, $description = '', $bizRule = null, $data = null)
	{
		return $this->createItem($name, Item::TYPE_OPERATION, $description, $bizRule, $data);
	}

	/**
	 * Returns roles.
	 * This is a shortcut method to [[Manager::getItems()]].
	 * @param mixed $userId the user ID. If not null, only the roles directly assigned to the user
	 * will be returned. Otherwise, all roles will be returned.
	 * @return Item[] roles (name => AuthItem)
	 */
	public function getRoles($userId = null)
	{
		return $this->getItems($userId, Item::TYPE_ROLE);
	}

	/**
	 * Returns tasks.
	 * This is a shortcut method to [[Manager::getItems()]].
	 * @param mixed $userId the user ID. If not null, only the tasks directly assigned to the user
	 * will be returned. Otherwise, all tasks will be returned.
	 * @return Item[] tasks (name => AuthItem)
	 */
	public function getTasks($userId = null)
	{
		return $this->getItems($userId, Item::TYPE_TASK);
	}

	/**
	 * Returns operations.
	 * This is a shortcut method to [[Manager::getItems()]].
	 * @param mixed $userId the user ID. If not null, only the operations directly assigned to the user
	 * will be returned. Otherwise, all operations will be returned.
	 * @return Item[] operations (name => AuthItem)
	 */
	public function getOperations($userId = null)
	{
		return $this->getItems($userId, Item::TYPE_OPERATION);
	}

	/**
	 * Executes the specified business rule.
	 * @param string $bizRule the business rule to be executed.
	 * @param array $params parameters passed to [[Manager::checkAccess()]].
	 * @param mixed $data additional data associated with the authorization item or assignment.
	 * @return boolean whether the business rule returns true.
	 * If the business rule is empty, it will still return true.
	 */
	public function executeBizRule($bizRule, $params, $data)
	{
		return $bizRule === '' || $bizRule === null || ($this->showErrors ? eval($bizRule) != 0 : @eval($bizRule) != 0);
	}

	/**
	 * Checks the item types to make sure a child can be added to a parent.
	 * @param integer $parentType parent item type
	 * @param integer $childType child item type
	 * @throws InvalidParamException if the item cannot be added as a child due to its incompatible type.
	 */
	protected function checkItemChildType($parentType, $childType)
	{
		static $types = array('operation', 'task', 'role');
		if ($parentType < $childType) {
			throw new InvalidParamException("Cannot add an item of type '$types[$childType]' to an item of type '$types[$parentType]'.");
		}
	}

	/**
	 * Performs access check for the specified user.
	 * @param mixed $userId the user ID. This should be either an integer or a string representing
	 * the unique identifier of a user. See [[User::id]].
	 * @param string $itemName the name of the operation that we are checking access to
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by the user.
	 */
	abstract public function checkAccess($userId, $itemName, $params = array());

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
	 * @throws \yii\base\Exception if an item with the same name already exists
	 * @return Item the authorization item
	 */
	abstract public function createItem($name, $type, $description = '', $bizRule = null, $data = null);
	/**
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	abstract public function removeItem($name);
	/**
	 * Returns the authorization items of the specific type and user.
	 * @param mixed $userId the user ID. Defaults to null, meaning returning all items even if
	 * they are not assigned to a user.
	 * @param integer $type the item type (0: operation, 1: task, 2: role). Defaults to null,
	 * meaning returning all items regardless of their type.
	 * @return Item[] the authorization items of the specific type.
	 */
	abstract public function getItems($userId = null, $type = null);
	/**
	 * Returns the authorization item with the specified name.
	 * @param string $name the name of the item
	 * @return Item the authorization item. Null if the item cannot be found.
	 */
	abstract public function getItem($name);
	/**
	 * Saves an authorization item to persistent storage.
	 * @param Item $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 */
	abstract public function saveItem($item, $oldName = null);

	/**
	 * Adds an item as a child of another item.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @throws \yii\base\Exception if either parent or child doesn't exist or if a loop has been detected.
	 */
	abstract public function addItemChild($itemName, $childName);
	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the removal is successful
	 */
	abstract public function removeItemChild($itemName, $childName);
	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	abstract public function hasItemChild($itemName, $childName);
	/**
	 * Returns the children of the specified item.
	 * @param mixed $itemName the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names.
	 * @return Item[] all child items of the parent
	 */
	abstract public function getItemChildren($itemName);

	/**
	 * Assigns an authorization item to a user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @param string $bizRule the business rule to be executed when [[checkAccess()]] is called
	 * for this particular authorization item.
	 * @param mixed $data additional data associated with this assignment
	 * @return Assignment the authorization assignment information.
	 * @throws \yii\base\Exception if the item does not exist or if the item has already been assigned to the user
	 */
	abstract public function assign($userId, $itemName, $bizRule = null, $data = null);
	/**
	 * Revokes an authorization assignment from a user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return boolean whether removal is successful
	 */
	abstract public function revoke($userId, $itemName);
	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return boolean whether the item has been assigned to the user.
	 */
	abstract public function isAssigned($userId, $itemName);
	/**
	 * Returns the item assignment information.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return Assignment the item assignment information. Null is returned if
	 * the item is not assigned to the user.
	 */
	abstract public function getAssignment($userId, $itemName);
	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @return Item[] the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	abstract public function getAssignments($userId);
	/**
	 * Saves the changes to an authorization assignment.
	 * @param Assignment $assignment the assignment that has been changed.
	 */
	abstract public function saveAssignment($assignment);
	/**
	 * Removes all authorization data.
	 */
	abstract public function clearAll();
	/**
	 * Removes all authorization assignments.
	 */
	abstract public function clearAssignments();
	/**
	 * Saves authorization data into persistent storage.
	 * If any change is made to the authorization data, please make
	 * sure you call this method to save the changed data into persistent storage.
	 */
	abstract public function save();
}
