<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

/**
 * IManager interface is implemented by an auth manager application component.
 *
 * An auth manager is mainly responsible for providing role-based access control (RBAC) service.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
interface IManager
{
	/**
	 * Performs access check for the specified user.
	 * @param mixed $userId the user ID. This should be either an integer or a string representing
	 * the unique identifier of a user. See [[User::id]].
	 * @param string $itemName the name of the operation that we are checking access to
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by the user.
	 */
	public function checkAccess($userId, $itemName, $params = array());

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
	public function createItem($name, $type, $description = '', $bizRule = null, $data = null);
	/**
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	public function removeItem($name);
	/**
	 * Returns the authorization items of the specific type and user.
	 * @param mixed $userId the user ID. Defaults to null, meaning returning all items even if
	 * they are not assigned to a user.
	 * @param integer $type the item type (0: operation, 1: task, 2: role). Defaults to null,
	 * meaning returning all items regardless of their type.
	 * @return Item[] the authorization items of the specific type.
	 */
	public function getItems($userId = null, $type = null);
	/**
	 * Returns the authorization item with the specified name.
	 * @param string $name the name of the item
	 * @return Item the authorization item. Null if the item cannot be found.
	 */
	public function getItem($name);
	/**
	 * Saves an authorization item to persistent storage.
	 * @param Item $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 */
	public function saveItem($item, $oldName = null);

	/**
	 * Adds an item as a child of another item.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @throws \yii\base\Exception if either parent or child doesn't exist or if a loop has been detected.
	 */
	public function addItemChild($itemName, $childName);
	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the removal is successful
	 */
	public function removeItemChild($itemName, $childName);
	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	public function hasItemChild($itemName, $childName);
	/**
	 * Returns the children of the specified item.
	 * @param mixed $itemName the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names.
	 * @return Item[] all child items of the parent
	 */
	public function getItemChildren($itemName);

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
	public function assign($userId, $itemName, $bizRule = null, $data = null);
	/**
	 * Revokes an authorization assignment from a user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return boolean whether removal is successful
	 */
	public function revoke($userId, $itemName);
	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return boolean whether the item has been assigned to the user.
	 */
	public function isAssigned($userId, $itemName);
	/**
	 * Returns the item assignment information.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return Assignment the item assignment information. Null is returned if
	 * the item is not assigned to the user.
	 */
	public function getAssignment($userId, $itemName);
	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @return Item[] the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	public function getAssignments($userId);
	/**
	 * Saves the changes to an authorization assignment.
	 * @param Assignment $assignment the assignment that has been changed.
	 */
	public function saveAssignment($assignment);
	/**
	 * Removes all authorization data.
	 */
	public function clearAll();
	/**
	 * Removes all authorization assignments.
	 */
	public function clearAssignments();
	/**
	 * Saves authorization data into persistent storage.
	 * If any change is made to the authorization data, please make
	 * sure you call this method to save the changed data into persistent storage.
	 */
	public function save();
	/**
	 * Executes a business rule.
	 * A business rule is a piece of PHP code that will be executed when [[checkAccess()]] is called.
	 * @param string $bizRule the business rule to be executed.
	 * @param array $params additional parameters to be passed to the business rule when being executed.
	 * @param mixed $data additional data that is associated with the corresponding authorization item or assignment
	 * @return boolean whether the execution returns a true value.
	 * If the business rule is empty, it will also return true.
	 */
	public function executeBizRule($bizRule, $params, $data);
}
