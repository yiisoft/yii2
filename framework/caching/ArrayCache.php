<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ArrayCache provides caching for the current request only by storing the values in an array.
 *
 * See [[Cache]] for common cache operations that ArrayCache supports.
 *
 * Unlike the [[Cache]], ArrayCache allows the expire parameter of [[set]], [[add]], [[mset]] and [[madd]] to
 * be a floating point number, so you may specify the time in milliseconds (e.g. 0.1 will be 100 milliseconds).
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ArrayCache extends Cache
{
    private $_cache;


    /**
     * @inheritdoc
     */
    public function exists($key)
    {
        $key = $this->buildKey($key);
        return isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true));
    }

    /**
     * @inheritdoc
     */
    protected function getValue($key)
    {
        if (isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true))) {
            return $this->_cache[$key][0];
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    protected function setValue($key, $value, $duration)
    {
        $this->_cache[$key] = [$value, $duration === 0 ? 0 : microtime(true) + $duration];
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function addValue($key, $value, $duration)
    {
        if (isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true))) {
            return false;
        } else {
            $this->_cache[$key] = [$value, $duration === 0 ? 0 : microtime(true) + $duration];
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    protected function deleteValue($key)
    {
        unset($this->_cache[$key]);
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function flushValues()
    {
        $this->_cache = [];
        return true;
    }
}
