<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ArrayAccessTrait 为 [[\IteratorAggregate]]，[[\ArrayAccess]] 和 [[\Countable]] 提供实现。
 *
 * 请注意，ArrayAccessTrait 要求使用它的类包含一个名为 `data` 的属性，该属性应该是一个数组。
 * ArrayAccessTrait 将公开数据以支持像数组一样访问类对象。
 *
 * @property array $data
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
trait ArrayAccessTrait
{
    /**
     * 返回遍历数据的迭代器。
     * SPL 接口 [[\IteratorAggregate]] 需要此方法。
     * 当您使用 `foreach` 遍历集合时，将隐式调用它。
     * @return \ArrayIterator 遍历集合中 cookies 的迭代器。
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * 返回数据项的数量。
     * Countable 接口需要此方法。
     * @return int 数据元素的数量。
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * 接口 [[\ArrayAccess]] 需要此方法。
     * @param mixed $offset 要检查的偏移量
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * 接口 [[\ArrayAccess]] 需要此方法。
     * @param int $offset 检索元素的偏移量。
     * @return mixed 偏移处的元素，如果在偏移处找不到元素，则返回 null
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * 接口 [[\ArrayAccess]] 需要此方法。
     * @param int $offset 设置元素的偏移量
     * @param mixed $item 元素的值
     */
    public function offsetSet($offset, $item)
    {
        $this->data[$offset] = $item;
    }

    /**
     * 接口 [[\ArrayAccess]] 需要此方法。
     * @param mixed $offset 未设置元素的偏移量
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
