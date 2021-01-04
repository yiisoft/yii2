<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

use yii\base\Action;

interface ActionParameterBinderInterface
{
    /**
     * @param Action $action
     * @param array $params
     * @return ActionBindingResult
     */
    public function bindActionParams($action, $params);
}
