<?php

namespace yiiunit\framework\rbac;

use yii\rbac\Rule;

/**
 * Description of ActionRule
 */
class ActionRule extends Rule
{
    public $name = 'action_rule';
    public $action = 'read';

    private $somePrivateProperty;
    protected $someProtectedProperty;

    public function execute($user, $item, $params)
    {
        return $this->action === 'all' || $this->action === $params['action'];
    }
}
