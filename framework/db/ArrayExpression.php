<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Traversable;
use yii\base\InvalidConfigException;

/**
 * ArrayExpression 类表示数组的 SQL 表达式。
 *
 * 此类表达式也可以用于以下条件：
 *
 * ```php
 * $query->andWhere(['@>', 'items', new ArrayExpression([1, 2, 3], 'integer')])
 * ```
 *
 * 这取决于 DBMS，将实现良好的准备条件。例如，
 * 在 PostgreSQL 中，它将被编译为 `WHERE "items" @> ARRAY[1, 2, 3]::integer[]`。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class ArrayExpression implements ExpressionInterface, \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @var null|string 数组元素的类型。
     * 默认为 `null`，表示未明确指定类型。
     *
     * 请注意，如果未明确指定 type 并且 DBMS 无法从上下文中猜出它，
     * 则会引起 SQL 错误。
     */
    private $type;
    /**
     * @var array|QueryInterface 数组的内容。
     * In 可以表示值为数组或 [[Query]] 返回的这些值。
     */
    private $value;
    /**
     * @var int 选择元素所需的索引数
     */
    private $dimension;


    /**
     * ArrayExpression 构造函数。
     *
     * @param array|QueryInterface|mixed $value 数组内容。表示为值数组或返回这些值的 Query。
     * 单个值将被视为包含一个元素的数组。
     * @param string|null $type 数组元素的类型。默认为 `null`，
     * 表示未明确指定类型。如果未明确指定 type 并且 DBMS 无法从上下文中猜出它，
     * 则会引起 SQL 错误。
     * @param int $dimension 选择元素所需的索引数
     */
    public function __construct($value, $type = null, $dimension = 1)
    {
        if ($value instanceof self) {
            $value = $value->getValue();
        }

        $this->value = $value;
        $this->type = $type;
        $this->dimension = $dimension;
    }

    /**
     * @return null|string
     * @see type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array|mixed|QueryInterface
     * @see value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int 选择元素所需的索引数
     * @see dimensions
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * 是否存在偏移量
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * 要检查的偏移量。
     * </p>
     * @return bool 成功时为 true，失败为 false。
     * </p>
     * <p>
     * 如果返回非布尔值，则返回值将被转换为 boolean。
     * @since 2.0.14
     */
    public function offsetExists($offset)
    {
        return isset($this->value[$offset]);
    }

    /**
     * 要检索的偏移量
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * 要检索的偏移量。
     * </p>
     * @return mixed 可以返回所有值类型。
     * @since 2.0.14
     */
    public function offsetGet($offset)
    {
        return $this->value[$offset];
    }

    /**
     * 偏移量设置
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * 要赋值的偏移量。
     * </p>
     * @param mixed $value <p>
     * 要设置的值。
     * </p>
     * @return void
     * @since 2.0.14
     */
    public function offsetSet($offset, $value)
    {
        $this->value[$offset] = $value;
    }

    /**
     * 删除偏移量
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * 要删除的偏移量。
     * </p>
     * @return void
     * @since 2.0.14
     */
    public function offsetUnset($offset)
    {
        unset($this->value[$offset]);
    }

    /**
     * 计算对象的元素
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int 自定义计数为整数。
     * </p>
     * <p>
     * 返回值转换为整数。
     * @since 2.0.14
     */
    public function count()
    {
        return count($this->value);
    }

    /**
     * 检索外部迭代器
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable 实现 <b> 迭代器 </b> 或
     * <b> 可遍历 </b> 的对象实例
     * @since 2.0.14.1
     * @throws InvalidConfigException 当 ArrayExpression 包含 QueryInterface 对象时
     */
    public function getIterator()
    {
        $value = $this->getValue();
        if ($value instanceof QueryInterface) {
            throw new InvalidConfigException('The ArrayExpression class can not be iterated when the value is a QueryInterface object');
        }
        if ($value === null) {
            $value = [];
        }

        return new \ArrayIterator($value);
    }
}
