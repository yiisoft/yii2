<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

class AnyCaseValue extends CompareValue
{
    public $value;

    /**
     * Constructor.
     * @param string|string[] $value
     * @param array $config
     */
    public function __construct($value, $config = [])
    {
        if (\is_array($value)) {
            $this->value = array_map('strtolower', $value);
        } else {
            $this->value = strtolower($value);
        }
        parent::__construct($config);
    }
}
