<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

final class ActionBindingResult
{
    /**
     * @var BindingParameter[]
     */
    public $parameters = [];

    /**
     * @var array
     */
    public $arguments = [];

    /**
     * @var array
     */
    public $missing = [];
}
