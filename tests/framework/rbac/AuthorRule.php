<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use yii\rbac\Rule;

/**
 * Checks if authorID matches userID passed via params
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor';
    public $reallyReally = false;

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        return $params['authorID'] == $user;
    }
}
