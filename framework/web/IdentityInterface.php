<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * IdentityInterface is the interface that should be implemented by a class providing identity information.
 *
 * This interface can typically be implemented by a user model class. For example, the following
 * code shows how to implement this interface by a User ActiveRecord class:
 *
 * ```php
 * class User extends ActiveRecord implements IdentityInterface
 * {
 *     public static function findIdentity($id)
 *     {
 *         return static::findOne($id);
 *     }
 *
 *     public static function findIdentityByAccessToken($token, $type = null)
 *     {
 *         return static::findOne(['access_token' => $token]);
 *     }
 *
 *     public function getId()
 *     {
 *         return $this->id;
 *     }
 *
 *     public function getAuthKey()
 *     {
 *         return $this->authKey;
 *     }
 *
 *     public function validateAuthKey($authKey)
 *     {
 *         return $this->authKey === $authKey;
 *     }
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface IdentityInterface
{
    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id);

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface|null the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null);

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId();

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled. The returned key will be stored on the
     * client side as a cookie and will be used to authenticate user even if PHP session has been expired.
     *
     * Make sure to invalidate earlier issued authKeys when you implement force user logout, password change and
     * other scenarios, that require forceful access revocation for old sessions.
     *
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey();

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey);
}
