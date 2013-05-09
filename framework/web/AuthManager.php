<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\base\Exception;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
abstract class AuthManager extends Component implements IAuthManager
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
	 * This is a shortcut method to [[IAuthManager::createAuthItem()]].
	 * @param string $name the item name
	 * @param string $description the item description.
	 * @param string $bizRule the business rule associated with this item
	 * @param mixed $data additional data to be passed when evaluating the business rule
	 * @return AuthItem the authorization item
	 */
	public function createRole($name, $description = '', $bizRule = null, $data = null)
	{
		return $this->createAuthItem($name, AuthItem::TYPE_ROLE, $description, $bizRule, $data);
	}

	/**
	 * Creates a task.
	 * This is a shortcut method to [[IAuthManager::createAuthItem()]].
	 * @param string $name the item name
	 * @param string $description the item description.
	 * @param string $bizRule the business rule associated with this item
	 * @param mixed $data additional data to be passed when evaluating the business rule
	 * @return AuthItem the authorization item
	 */
	public function createTask($name, $description = '', $bizRule = null, $data = null)
	{
		return $this->createAuthItem($name, AuthItem::TYPE_TASK, $description, $bizRule, $data);
	}

	/**
	 * Creates an operation.
	 * This is a shortcut method to [[IAuthManager::createAuthItem()]].
	 * @param string $name the item name
	 * @param string $description the item description.
	 * @param string $bizRule the business rule associated with this item
	 * @param mixed $data additional data to be passed when evaluating the business rule
	 * @return AuthItem the authorization item
	 */
	public function createOperation($name, $description = '', $bizRule = null, $data = null)
	{
		return $this->createAuthItem($name, AuthItem::TYPE_OPERATION, $description, $bizRule, $data);
	}

	/**
	 * Returns roles.
	 * This is a shortcut method to [[IAuthManager::getAuthItems()]].
	 * @param mixed $userId the user ID. If not null, only the roles directly assigned to the user
	 * will be returned. Otherwise, all roles will be returned.
	 * @return array roles (name=>AuthItem)
	 */
	public function getRoles($userId = null)
	{
		return $this->getAuthItems(AuthItem::TYPE_ROLE, $userId);
	}

	/**
	 * Returns tasks.
	 * This is a shortcut method to [[IAuthManager::getAuthItems()]].
	 * @param mixed $userId the user ID. If not null, only the tasks directly assigned to the user
	 * will be returned. Otherwise, all tasks will be returned.
	 * @return array tasks (name=>AuthItem)
	 */
	public function getTasks($userId = null)
	{
		return $this->getAuthItems(AuthItem::TYPE_TASK, $userId);
	}

	/**
	 * Returns operations.
	 * This is a shortcut method to [[IAuthManager::getAuthItems()]].
	 * @param mixed $userId the user ID. If not null, only the operations directly assigned to the user
	 * will be returned. Otherwise, all operations will be returned.
	 * @return array operations (name=>AuthItem)
	 */
	public function getOperations($userId = null)
	{
		return $this->getAuthItems(AuthItem::TYPE_OPERATION, $userId);
	}

	/**
	 * Executes the specified business rule.
	 * @param string $bizRule the business rule to be executed.
	 * @param array $params parameters passed to [[IAuthManager::checkAccess()]].
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
	 * @throws Exception if the item cannot be added as a child due to its incompatible type.
	 */
	protected function checkItemChildType($parentType, $childType)
	{
		static $types = array('operation', 'task', 'role');
		if ($parentType < $childType) {
			throw new Exception("Cannot add an item of type '$types[$childType]' to an item of type '$types[$parentType]'.");
		}
	}
}
