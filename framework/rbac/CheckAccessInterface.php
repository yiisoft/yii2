<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

/**
 * 有关 CheckAccessInterface 的更多详细信息和用法信息，请参阅 [授权指南](guide:security-authorization)。
 *
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.9
 */
interface CheckAccessInterface
{
    /**
     * 检查用户是否具有指定的权限。
     * @param string|int $user 用户 ID。这应该是整数或字符串，
     * 表示用户的唯一标识符。参阅 [[\yii\web\User::id]]。
     * @param string $permissionName 要检查的权限名称
     * @param array $params 键值对数组，
     * 将传递给与用户分配的角色和权限相关的规则。
     * @return bool 用户是否具有指定的权限。
     * @throws \yii\base\InvalidParamException 如果 $permissionName 不是现有的权限名称
     */
    public function checkAccess($userId, $permissionName, $params = []);
}
