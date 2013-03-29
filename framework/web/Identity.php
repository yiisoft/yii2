<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface Identity
{
	/**
	 * Returns an ID that can uniquely identify a user identity.
	 * @return string|integer an ID that uniquely identifies a user identity.
	 */
	public function getId();
	/**
	 * Returns a key that can be used to check the validity of a given identity ID.
	 * The space of such keys should be big and random enough to defeat potential identity attacks.
	 * The returned key can be a string, an integer, or any serializable data.
	 * @return string a key that is used to check the validity of a given identity ID.
	 * @see validateAuthKey()
	 */
	public function getAuthKey();
	/**
	 * Validates the given auth key.
	 * @param string $authKey the given auth key
	 * @return boolean whether the given auth key is valid.
	 * @see getAuthKey()
	 */
	public function validateAuthKey($authKey);
	/**
	 * Finds an identity by the given ID.
	 * @param string|integer $id the ID to be looked for
	 * @return Identity the identity object that matches the given ID.
	 * Null should be returned if such an identity cannot be found.
	 */
	public static function findIdentity($id);
}