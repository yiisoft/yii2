<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use Yii;
use yii\base\Object;

/**
 * Item represents an authorization item.
 * An authorization item can be an operation, a task or a role.
 * They form an authorization hierarchy. Items on higher levels of the hierarchy
 * inherit the permissions represented by items on lower levels.
 * A user may be assigned one or several authorization items (called [[Assignment]] assignments).
 * He can perform an operation only when it is among his assigned items.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Item extends Object
{
	const TYPE_OPERATION = 0;
	const TYPE_TASK = 1;
	const TYPE_ROLE = 2;

	/**
	 * @var Manager the auth manager of this item
	 */
	public $manager;
	/**
	 * @var string the item description
	 */
	public $description;
	/**
	 * @var string the business rule associated with this item
	 */
	public $bizRule;
	/**
	 * @var mixed the additional data associated with this item
	 */
	public $data;
	/**
	 * @var integer the authorization item type. This could be 0 (operation), 1 (task) or 2 (role).
	 */
	public $type;

	private $_name;
	private $_oldName;


	/**
	 * Checks to see if the specified item is within the hierarchy starting from this item.
	 * This method is expected to be internally used by the actual implementations
	 * of the [[Manager::checkAccess()]].
	 * @param string $itemName the name of the item to be checked
	 * @param array $params the parameters to be passed to business rule evaluation
	 * @return boolean whether the specified item is within the hierarchy starting from this item.
	 */
	public function checkAccess($itemName, $params = array())
	{
		Yii::trace('Checking permission: ' . $this->_name, __METHOD__);
		if ($this->manager->executeBizRule($this->bizRule, $params, $this->data)) {
			if ($this->_name == $itemName) {
				return true;
			}
			foreach ($this->manager->getItemChildren($this->_name) as $item) {
				if ($item->checkAccess($itemName, $params)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @return string the item name
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @param string $value the item name
	 */
	public function setName($value)
	{
		if ($this->_name !== $value) {
			$this->_oldName = $this->_name;
			$this->_name = $value;
		}
	}

	/**
	 * Adds a child item.
	 * @param string $name the name of the child item
	 * @return boolean whether the item is added successfully
	 * @throws \yii\base\Exception if either parent or child doesn't exist or if a loop has been detected.
	 * @see Manager::addItemChild
	 */
	public function addChild($name)
	{
		return $this->manager->addItemChild($this->_name, $name);
	}

	/**
	 * Removes a child item.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $name the child item name
	 * @return boolean whether the removal is successful
	 * @see Manager::removeItemChild
	 */
	public function removeChild($name)
	{
		return $this->manager->removeItemChild($this->_name, $name);
	}

	/**
	 * Returns a value indicating whether a child exists
	 * @param string $name the child item name
	 * @return boolean whether the child exists
	 * @see Manager::hasItemChild
	 */
	public function hasChild($name)
	{
		return $this->manager->hasItemChild($this->_name, $name);
	}

	/**
	 * Returns the children of this item.
	 * @return Item[] all child items of this item.
	 * @see Manager::getItemChildren
	 */
	public function getChildren()
	{
		return $this->manager->getItemChildren($this->_name);
	}

	/**
	 * Assigns this item to a user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $bizRule the business rule to be executed when [[checkAccess()]] is called
	 * for this particular authorization item.
	 * @param mixed $data additional data associated with this assignment
	 * @return Assignment the authorization assignment information.
	 * @throws \yii\base\Exception if the item has already been assigned to the user
	 * @see Manager::assign
	 */
	public function assign($userId, $bizRule = null, $data = null)
	{
		return $this->manager->assign($userId, $this->_name, $bizRule, $data);
	}

	/**
	 * Revokes an authorization assignment from a user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @return boolean whether removal is successful
	 * @see Manager::revoke
	 */
	public function revoke($userId)
	{
		return $this->manager->revoke($userId, $this->_name);
	}

	/**
	 * Returns a value indicating whether this item has been assigned to the user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @return boolean whether the item has been assigned to the user.
	 * @see Manager::isAssigned
	 */
	public function isAssigned($userId)
	{
		return $this->manager->isAssigned($userId, $this->_name);
	}

	/**
	 * Returns the item assignment information.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @return Assignment the item assignment information. Null is returned if
	 * this item is not assigned to the user.
	 * @see Manager::getAssignment
	 */
	public function getAssignment($userId)
	{
		return $this->manager->getAssignment($userId, $this->_name);
	}

	/**
	 * Saves an authorization item to persistent storage.
	 */
	public function save()
	{
		$this->manager->saveItem($this, $this->_oldName);
		unset($this->_oldName);
	}
}
