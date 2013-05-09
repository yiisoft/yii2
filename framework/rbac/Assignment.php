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
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Assignment extends Object
{
	private $_auth;
	private $_itemName;
	private $_userId;
	private $_bizRule;
	private $_data;

	/**
	 * Constructor.
	 * @param IManager $auth the authorization manager
	 * @param string $itemName authorization item name
	 * @param mixed $userId user ID (see [[User::id]])
	 * @param string $bizRule the business rule associated with this assignment
	 * @param mixed $data additional data for this assignment
	 */
	public function __construct($auth, $itemName, $userId, $bizRule = null, $data = null)
	{
		$this->_auth = $auth;
		$this->_itemName = $itemName;
		$this->_userId = $userId;
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
			$this->_auth->saveAssignment($this);
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
			$this->_auth->saveAssignment($this);
		}
	}
}
