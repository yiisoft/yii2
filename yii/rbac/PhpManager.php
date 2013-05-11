<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use Yii;
use yii\base\Exception;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;

/**
 * PhpManager represents an authorization manager that stores authorization
 * information in terms of a PHP script file.
 *
 * The authorization data will be saved to and loaded from a file
 * specified by [[authFile]], which defaults to 'protected/data/rbac.php'.
 *
 * PhpManager is mainly suitable for authorization data that is not too big
 * (for example, the authorization data for a personal blog system).
 * Use [[DbManager]] for more complex authorization data.
 *
 * @property array $authItems The authorization items of the specific type.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class PhpManager extends Manager
{
	/**
	 * @var string the path of the PHP script that contains the authorization data.
	 * If not set, it will be using 'protected/data/rbac.php' as the data file.
	 * Make sure this file is writable by the Web server process if the authorization
	 * needs to be changed.
	 * @see loadFromFile
	 * @see saveToFile
	 */
	public $authFile;

	private $_items = array(); // itemName => item
	private $_children = array(); // itemName, childName => child
	private $_assignments = array(); // userId, itemName => assignment

	/**
	 * Initializes the application component.
	 * This method overrides parent implementation by loading the authorization data
	 * from PHP script.
	 */
	public function init()
	{
		parent::init();
		if ($this->authFile === null) {
			$this->authFile = Yii::getAlias('@app/data/rbac') . '.php';
		}
		$this->load();
	}

	/**
	 * Performs access check for the specified user.
	 * @param mixed $userId the user ID. This can be either an integer or a string representing
	 * @param string $itemName the name of the operation that need access check
	 * the unique identifier of a user. See [[User::id]].
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user. A param with name 'userId' is added to
	 * this array, which holds the value of `$userId`.
	 * @return boolean whether the operations can be performed by the user.
	 */
	public function checkAccess($userId, $itemName, $params = array())
	{
		if (!isset($this->_items[$itemName])) {
			return false;
		}
		/** @var $item Item */
		$item = $this->_items[$itemName];
		Yii::trace('Checking permission: ' . $item->getName(), __METHOD__);
		if (!isset($params['userId'])) {
			$params['userId'] = $userId;
		}
		if ($this->executeBizRule($item->getBizRule(), $params, $item->getData())) {
			if (in_array($itemName, $this->defaultRoles)) {
				return true;
			}
			if (isset($this->_assignments[$userId][$itemName])) {
				/** @var $assignment Assignment */
				$assignment = $this->_assignments[$userId][$itemName];
				if ($this->executeBizRule($assignment->getBizRule(), $params, $assignment->getData())) {
					return true;
				}
			}
			foreach ($this->_children as $parentName => $children) {
				if (isset($children[$itemName]) && $this->checkAccess($userId, $parentName, $params)) {
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
	 * @throws InvalidCallException if item already has a child with $itemName or if a loop has been detected.
	 */
	public function addItemChild($itemName, $childName)
	{
		if (!isset($this->_items[$childName], $this->_items[$itemName])) {
			throw new Exception("Either '$itemName' or '$childName' does not exist.");
		}
		/** @var $child Item */
		$child = $this->_items[$childName];
		/** @var $item Item */
		$item = $this->_items[$itemName];
		$this->checkItemChildType($item->getType(), $child->getType());
		if ($this->detectLoop($itemName, $childName)) {
			throw new InvalidCallException("Cannot add '$childName' as a child of '$itemName'. A loop has been detected.");
		}
		if (isset($this->_children[$itemName][$childName])) {
			throw new InvalidCallException("The item '$itemName' already has a child '$childName'.");
		}
		$this->_children[$itemName][$childName] = $this->_items[$childName];
		return true;
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
		if (isset($this->_children[$itemName][$childName])) {
			unset($this->_children[$itemName][$childName]);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	public function hasItemChild($itemName, $childName)
	{
		return isset($this->_children[$itemName][$childName]);
	}

	/**
	 * Returns the children of the specified item.
	 * @param mixed $names the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names.
	 * @return Item[] all child items of the parent
	 */
	public function getItemChildren($names)
	{
		if (is_string($names)) {
			return isset($this->_children[$names]) ? $this->_children[$names] : array();
		}

		$children = array();
		foreach ($names as $name) {
			if (isset($this->_children[$name])) {
				$children = array_merge($children, $this->_children[$name]);
			}
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
		if (!isset($this->_items[$itemName])) {
			throw new InvalidParamException("Unknown authorization item '$itemName'.");
		} elseif (isset($this->_assignments[$userId][$itemName])) {
			throw new InvalidParamException("Authorization item '$itemName' has already been assigned to user '$userId'.");
		} else {
			return $this->_assignments[$userId][$itemName] = new Assignment($this, $userId, $itemName, $bizRule, $data);
		}
	}

	/**
	 * Revokes an authorization assignment from a user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return boolean whether removal is successful
	 */
	public function revoke($userId, $itemName)
	{
		if (isset($this->_assignments[$userId][$itemName])) {
			unset($this->_assignments[$userId][$itemName]);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @param string $itemName the item name
	 * @return boolean whether the item has been assigned to the user.
	 */
	public function isAssigned($userId, $itemName)
	{
		return isset($this->_assignments[$userId][$itemName]);
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
		return isset($this->_assignments[$userId][$itemName]) ? $this->_assignments[$userId][$itemName] : null;
	}

	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed $userId the user ID (see [[User::id]])
	 * @return Assignment[] the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	public function getAssignments($userId)
	{
		return isset($this->_assignments[$userId]) ? $this->_assignments[$userId] : array();
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
		if ($userId === null && $type === null) {
			return $this->_items;
		}
		$items = array();
		if ($userId === null) {
			foreach ($this->_items as $name => $item) {
				/** @var $item Item */
				if ($item->getType() == $type) {
					$items[$name] = $item;
				}
			}
		} elseif (isset($this->_assignments[$userId])) {
			foreach ($this->_assignments[$userId] as $assignment) {
				/** @var $assignment Assignment */
				$name = $assignment->getItemName();
				if (isset($this->_items[$name]) && ($type === null || $this->_items[$name]->getType() == $type)) {
					$items[$name] = $this->_items[$name];
				}
			}
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
		if (isset($this->_items[$name])) {
			throw new Exception('Unable to add an item whose name is the same as an existing item.');
		}
		return $this->_items[$name] = new Item($this, $name, $type, $description, $bizRule, $data);
	}

	/**
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	public function removeItem($name)
	{
		if (isset($this->_items[$name])) {
			foreach ($this->_children as &$children) {
				unset($children[$name]);
			}
			foreach ($this->_assignments as &$assignments) {
				unset($assignments[$name]);
			}
			unset($this->_items[$name]);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns the authorization item with the specified name.
	 * @param string $name the name of the item
	 * @return Item the authorization item. Null if the item cannot be found.
	 */
	public function getItem($name)
	{
		return isset($this->_items[$name]) ? $this->_items[$name] : null;
	}

	/**
	 * Saves an authorization item to persistent storage.
	 * @param Item $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 * @throws InvalidParamException if an item with the same name already taken
	 */
	public function saveItem($item, $oldName = null)
	{
		if ($oldName !== null && ($newName = $item->getName()) !== $oldName) // name changed
		{
			if (isset($this->_items[$newName])) {
				throw new InvalidParamException("Unable to change the item name. The name '$newName' is already used by another item.");
			}
			if (isset($this->_items[$oldName]) && $this->_items[$oldName] === $item) {
				unset($this->_items[$oldName]);
				$this->_items[$newName] = $item;
				if (isset($this->_children[$oldName])) {
					$this->_children[$newName] = $this->_children[$oldName];
					unset($this->_children[$oldName]);
				}
				foreach ($this->_children as &$children) {
					if (isset($children[$oldName])) {
						$children[$newName] = $children[$oldName];
						unset($children[$oldName]);
					}
				}
				foreach ($this->_assignments as &$assignments) {
					if (isset($assignments[$oldName])) {
						$assignments[$newName] = $assignments[$oldName];
						unset($assignments[$oldName]);
					}
				}
			}
		}
	}

	/**
	 * Saves the changes to an authorization assignment.
	 * @param Assignment $assignment the assignment that has been changed.
	 */
	public function saveAssignment($assignment)
	{
	}

	/**
	 * Saves authorization data into persistent storage.
	 * If any change is made to the authorization data, please make
	 * sure you call this method to save the changed data into persistent storage.
	 */
	public function save()
	{
		$items = array();
		foreach ($this->_items as $name => $item) {
			/** @var $item Item */
			$items[$name] = array(
				'type' => $item->getType(),
				'description' => $item->getDescription(),
				'bizRule' => $item->getBizRule(),
				'data' => $item->getData(),
			);
			if (isset($this->_children[$name])) {
				foreach ($this->_children[$name] as $child) {
					/** @var $child Item */
					$items[$name]['children'][] = $child->getName();
				}
			}
		}

		foreach ($this->_assignments as $userId => $assignments) {
			foreach ($assignments as $name => $assignment) {
				/** @var $assignment Assignment */
				if (isset($items[$name])) {
					$items[$name]['assignments'][$userId] = array(
						'bizRule' => $assignment->getBizRule(),
						'data' => $assignment->getData(),
					);
				}
			}
		}

		$this->saveToFile($items, $this->authFile);
	}

	/**
	 * Loads authorization data.
	 */
	public function load()
	{
		$this->clearAll();

		$items = $this->loadFromFile($this->authFile);

		foreach ($items as $name => $item) {
			$this->_items[$name] = new Item($this, $name, $item['type'], $item['description'], $item['bizRule'], $item['data']);
		}

		foreach ($items as $name => $item) {
			if (isset($item['children'])) {
				foreach ($item['children'] as $childName) {
					if (isset($this->_items[$childName])) {
						$this->_children[$name][$childName] = $this->_items[$childName];
					}
				}
			}
			if (isset($item['assignments'])) {
				foreach ($item['assignments'] as $userId => $assignment) {
					$this->_assignments[$userId][$name] = new Assignment($this, $name, $userId, $assignment['bizRule'], $assignment['data']);
				}
			}
		}
	}

	/**
	 * Removes all authorization data.
	 */
	public function clearAll()
	{
		$this->clearAssignments();
		$this->_children = array();
		$this->_items = array();
	}

	/**
	 * Removes all authorization assignments.
	 */
	public function clearAssignments()
	{
		$this->_assignments = array();
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
		if (!isset($this->_children[$childName], $this->_items[$itemName])) {
			return false;
		}
		foreach ($this->_children[$childName] as $child) {
			/** @var $child Item */
			if ($this->detectLoop($itemName, $child->getName())) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Loads the authorization data from a PHP script file.
	 * @param string $file the file path.
	 * @return array the authorization data
	 * @see saveToFile
	 */
	protected function loadFromFile($file)
	{
		if (is_file($file)) {
			return require($file);
		} else {
			return array();
		}
	}

	/**
	 * Saves the authorization data to a PHP script file.
	 * @param array $data the authorization data
	 * @param string $file the file path.
	 * @see loadFromFile
	 */
	protected function saveToFile($data, $file)
	{
		file_put_contents($file, "<?php\nreturn " . var_export($data, true) . ";\n", LOCK_EX);
	}
}
