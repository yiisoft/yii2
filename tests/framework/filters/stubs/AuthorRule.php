<?php

namespace yiiunit\framework\filters\stubs;

use yii\rbac\Rule;

/**
 * Checks if authorId matcher user passwd via params
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor';

    /**
     * @param string|int $user the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        return isset($params['authorId']) ? $params['authorId'] == $user : false;
    }
}
