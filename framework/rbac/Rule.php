<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\base\Object;

/**
 * Rule represents a business constraint that may be associated with a role, permission or assignment.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
abstract class Rule extends Object
{
    /**
     * @var string name of the rule
     */
    public $name;
    /**
     * @var integer UNIX timestamp representing the rule creation time
     */
    public $createdAt;
    /**
     * @var integer UNIX timestamp representing the rule updating time
     */
    public $updatedAt;

    /**
     * Executes the rule.
     *
     * @param Item $item the auth item that this rule is associated with
     * @param array $params parameters passed to [[ManagerInterface::allow()]].
     * @return boolean a value indicating whether the rule permits the auth item it is associated with.
     */
    abstract public function execute($item, $params);
}
