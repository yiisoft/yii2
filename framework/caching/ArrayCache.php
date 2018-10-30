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
 * Unlike the [[Cache]], ArrayCache allows the expire parameter of [[set]], [[add]], [[multiSet]] and [[multiAdd]] to
 * be a floating point number, so you may specify the time in milliseconds (e.g. 0.1 will be 100 milliseconds).
 *
 * For enhanced performance of ArrayCache, you can disable serialization of the stored data by setting [[$serializer]] to `false`.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ArrayCache extends Cache
{
    private $_cache = [];

    /**
     * @var int the probability (parts per million) that garbage collection (GC) should be performed
     * when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
     * This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.
     */
    public $gcProbability = 100;

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        $key = $this->buildKey($key);
        $exists = isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true));

        if (!$exists){
            $this->deleteValue($key);
        }

        return $exists;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($key)
    {
        if (isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true))){
            return $this->_cache[$key][0];
        }

        $this->deleteValue($key);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function setValue($key, $value, $duration)
    {
        $this->_cache[$key] = [$value, $duration === 0 ? 0 : microtime(true) + $duration];
        $this->gc();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function addValue($key, $value, $duration)
    {
        if (isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true))){
            return false;
        }
        $this->_cache[$key] = [$value, $duration === 0 ? 0 : microtime(true) + $duration];
        $this->gc();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteValue($key)
    {
        unset($this->_cache[$key]);
        $this->gc();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function flushValues()
    {
        $this->_cache = [];

        return true;
    }


    /**
     * Removes the expired data values.
     * @param bool $force whether to enforce the garbage collection regardless of [[gcProbability]].
     * Defaults to false, meaning the actual deletion happens with the probability as specified by [[gcProbability]].
     */
    public function gc($force = false)
    {
        if ($force || mt_rand(0, 1000000) < $this->gcProbability){
            foreach($this->_cache as $key => $item){
                if (!isset($item[0], $item[1]) || (0 !== $item[0] && $item[1] < microtime(true))){
                    $this->deleteValue($key);
                }
            }
        }

        return true;
    }
}
