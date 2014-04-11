<?php
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
    public function execute($params, $data)
    {
        return $params['authorID'] == $params['userID'];
    }
}
 