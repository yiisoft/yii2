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
 * Assignment represents an assignment of a role to a user.
 * It includes additional assignment information such as [[bizRule]] and [[data]].
 * Do not create a Assignment instance using the 'new' operator.
 * Instead, call [[Manager::assign()]].
 *
 * @property mixed $userId User ID (see [[User::id]]).
 * @property string $itemName The authorization item name.
 * @property string $bizRule The business rule associated with this assignment.
 * @property mixed $data Additional data for this assignment.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Assignment extends Object
{
	private $_auth;
	private $_userId;
	private $_itemName;
	private $_bizRule;
	private $_data;

	/**
	 * Constructor.
	 * @param Manager $auth the authorization manager
	 * @param mixed $userId user ID (see [[User::id]])
	 * @param string $itemName authorization item name
	 * @param string $bizRule the business rule associated with this assignment
	 * @param mixed $data additional data for this assignment
	 */
	public function __construct($auth, $userId, $itemName, $bizRule = null, $data = null)
	{
		$this->_auth = $auth;
		$this->_userId = $userId;
		$this->_itemName = $itemName;
		$this->_bizRule = $bizRule;
		$this->_data = $data;
	}

	/**
	 * @return mixed user ID (see [[User::id]])
	 */
	public function getUserId()
	{
		return $this->_userId;
	}

	/**
	 * @return string the authorization item name
	 */
	public function getItemName()
	{
		return $this->_itemName;
	}

	/**
	 * @return string the business rule associated with this assignment
	 */
	public function getBizRule()
	{
		return $this->_bizRule;
	}

	/**
	 * @param string $value the business rule associated with this assignment
	 */
	public function setBizRule($value)
	{
		if ($this->_bizRule !== $value) {
			$this->_bizRule = $value;
		}
	}

	/**
	 * @return mixed additional data for this assignment
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * @param mixed $value additional data for this assignment
	 */
	public function setData($value)
	{
		if ($this->_data !== $value) {
			$this->_data = $value;
		}
	}

	/**
	 * Saves the changes to an authorization assignment.
	 */
	public function save()
	{
		$this->_auth->saveAssignment($this);
	}
}
