<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\base\BaseObject;

/**
 * 有关 Item 的更多详细信息和用法信息，请参阅 [授权指南](guide:security-authorization)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Item extends BaseObject
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;

    /**
     * @var int 项目的类型。其值为 [[TYPE_ROLE]] 或 [[TYPE_PERMISSION]]。
     */
    public $type;
    /**
     * @var string 项目的名称。这必须是全局都唯一。
     */
    public $name;
    /**
     * @var string 项目的描述
     */
    public $description;
    /**
     * @var string 与此项关联的规则的名称
     */
    public $ruleName;
    /**
     * @var mixed 与此项目关联的其他数据
     */
    public $data;
    /**
     * @var int UNIX 时间戳，表示项目创建时间
     */
    public $createdAt;
    /**
     * @var int UNIX 时间戳，表示项目更新时间
     */
    public $updatedAt;
}
