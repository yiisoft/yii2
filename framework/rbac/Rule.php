<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\base\BaseObject;

/**
 * Rule represents a business constraint that may be associated with a role, permission or assignment.
 *
 * For more details and usage information on Rule, see the [guide article on security authorization](guide:security-authorization).
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
abstract class Rule extends BaseObject
{
    /**
     * @var string name of the rule
     */
    public $name;
    /**
     * @var int UNIX timestamp representing the rule creation time
     */
    public $createdAt;
    /**
     * @var int UNIX timestamp representing the rule updating time
     */
    public $updatedAt;


    /**
     * Executes the rule.
     *
     * @param string|int $user the user ID. This should be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to [[CheckAccessInterface::checkAccess()]].
     * @return bool a value indicating whether the rule permits the auth item it is associated with.
     */
    abstract public function execute($user, $item, $params);
}
