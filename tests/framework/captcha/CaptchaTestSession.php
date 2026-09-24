<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\captcha;

use yii\web\Session;

class CaptchaTestSession extends Session
{
    private $_data = [];

    public function open()
    {
    }

    public function close()
    {
    }

    public function offsetGet($offset)
    {
        return $this->_data[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    public function remove($key)
    {
        $value = $this->_data[$key] ?? null;
        unset($this->_data[$key]);

        return $value;
    }
}
