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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Assignment extends Object
{
	/**
	 * @var Manager the auth manager of this item
	 */
	public $manager;
	/**
	 * @var string the business rule associated with this assignment
	 */
	public $bizRule;
	/**
	 * @var mixed additional data for this assignment
	 */
	public $data;
	/**
	 * @var mixed user ID (see [[User::id]]). Do not modify this property after it is populated.
	 * To modify the user ID of an assignment, you must remove the assignment and create a new one.
	 */
	public $userId;
	/**
	 * @return string the authorization item name. Do not modify this property after it is populated.
	 * To modify the item name of an assignment, you must remove the assignment and create a new one.
	 */
	public $itemName;

	/**
	 * Saves the changes to an authorization assignment.
	 */
	public function save()
	{
		$this->manager->saveAssignment($this);
	}
}
