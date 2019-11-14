<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\base\BaseObject;

/**
 * Rule 表示可能与角色，权限或分配相关的业务约束。
 *
 * 有关 Rule 的更多详细信息和用法信息，请参阅 [授权指南](guide:security-authorization)。
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
abstract class Rule extends BaseObject
{
    /**
     * @var string 规则的名称
     */
    public $name;
    /**
     * @var int UNIX 时间戳，代表规则的创建时间
     */
    public $createdAt;
    /**
     * @var int UNIX 时间戳，代表规则的更新时间
     */
    public $updatedAt;


    /**
     * 执行规则。
     *
     * @param string|int $user 用户 ID。这应该是整数或字符串，
     * 表示用户的唯一标识符。参阅 [[\yii\web\User::id]]。
     * @param Item $item 该规则关联的角色或许可
     * @param array $params 传递给 [[CheckAccessInterface::checkAccess()]] 的参数。
     * @return bool 该规则是否允许与此相关的身份验证项。
     */
    abstract public function execute($user, $item, $params);
}
