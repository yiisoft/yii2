<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use Yii;
use yii\base\BaseObject;

/**
 * Assignment 表示为用户分配角色。
 *
 * 有关 Assignment 的更多详细信息和用法信息，请参阅 [授权指南](guide:security-authorization)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class Assignment extends BaseObject
{
    /**
     * @var string|int 用户 ID（参阅 [[\yii\web\User::id]]）
     */
    public $userId;
    /**
     * @var string 角色名称
     */
    public $roleName;
    /**
     * @var int UNIX 时间戳，代表 Assignment 的创建时间
     */
    public $createdAt;
}
