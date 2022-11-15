<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\base\BaseObject;

/**
 * For more details and usage information on Item, see the [guide article on security authorization](guide:security-authorization).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Item extends BaseObject
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;

    /**
     * @var int the type of the item. This should be either [[TYPE_ROLE]] or [[TYPE_PERMISSION]].
     */
    public $type;
    /**
     * @var string the name of the item. This must be globally unique.
     */
    public $name;
    /**
     * @var string the item description
     */
    public $description;
    /**
     * @var string name of the rule associated with this item
     */
    public $ruleName;
    /**
     * @var mixed the additional data associated with this item
     */
    public $data;
    /**
     * @var int UNIX timestamp representing the item creation time
     */
    public $createdAt;
    /**
     * @var int UNIX timestamp representing the item updating time
     */
    public $updatedAt;
}
