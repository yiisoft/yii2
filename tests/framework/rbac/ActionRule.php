<?php

namespace yiiunit\framework\rbac;

/**
 * Description of ActionRule
 */
class ActionRule extends \yii\rbac\Rule
{
    public $name = 'action_rule';
    public $action = 'read';
    public function execute($user, $item, $params)
    {
        return $this->action === 'all' || $this->action === $params['action'];
    }
}
