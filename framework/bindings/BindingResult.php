<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bindings;

final class BindingResult
{
    /**
     * @var mixed $value
     */
    public $value;

    /**
     * @var mixed $value
     */
    public $message = null;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
