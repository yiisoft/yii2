<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

/**
 * 有关 Role 的更多详细信息和用法信息，请参阅 [授权指南](guide:security-authorization)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Role extends Item
{
    /**
     * {@inheritdoc}
     */
    public $type = self::TYPE_ROLE;
}
