<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

/**
 * For more details and usage information on CheckAccessInterface, see the [guide article on security authorization](guide:security-authorization).
 *
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.9
 */
interface CheckAccessInterface
{
    /**
     * Checks if the user has the specified permission.
     * @param string|int $userId the user ID. This should be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $permissionName the name of the permission to be checked against
     * @param array $params name-value pairs that will be passed to the rules associated
     * with the roles and permissions assigned to the user.
     * @return bool whether the user has the specified permission.
     * @throws \yii\base\InvalidArgumentException if $permissionName does not refer to an existing permission
     */
    public function checkAccess($userId, $permissionName, $params = []);
}
