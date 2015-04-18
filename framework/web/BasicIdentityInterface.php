<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * BasicIdentityInterface is the basic interface that should be implemented by a class providing identity information.
 * You need implement this interface instead IdentityInterface if You don`t need authorization key.
 *
 * This interface can typically be implemented by a user model class. For example, the following
 * code shows how to implement this interface by a User ActiveRecord class:
 *
 * ~~~
 * class User extends ActiveRecord implements BasicIdentityInterface
 * {
 *     public static function findIdentity($id)
 *     {
 *         return static::findOne($id);
 *     }
 *
 *     public function getId()
 *     {
 *         return $this->id;
 *     }
 * }
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Shkarbatov Dmitriy <shkarbatov@gmail.com>
 * @since 2.0.4
 */
interface BasicIdentityInterface
{
    /**
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return BasicIdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id);
    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId();
}
