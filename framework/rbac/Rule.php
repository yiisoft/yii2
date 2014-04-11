<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\base\Object;

/**
 * Rule represents a business constraint that may be assigned and the applied to
 * an authorization item or assignment.
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
     * Executes the rule.
     *
     * @param array $params parameters passed to [[Manager::checkAccess()]].
     * @param mixed $data additional data associated with the authorization item or assignment.
     * @return boolean whether the rule execution returns true.
     */
    abstract public function execute($params, $data);
}
