<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\BaseObject;
use yii\base\InvalidArgumentException;

/**
 * TableSchema 表示数据库表的元数据。
 *
 * @property array $columnNames 列名列表。此属性是只读的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TableSchema extends BaseObject
{
    /**
     * @var string 此表所属的结构的名称。
     */
    public $schemaName;
    /**
     * @var string the 表名。结构名称不包括在内。使用 [[fullName]] 获取带有结构名称前缀的名称。
     */
    public $name;
    /**
     * @var string 表的全名，包含结构名称前缀（如果有）。
     * 请注意，如果结构名称与 [[Schema::defaultSchema|default schema name]] 相同，
     * 则不包括结构名称。
     */
    public $fullName;
    /**
     * @var string[] 表的主键。
     */
    public $primaryKey = [];
    /**
     * @var string 主键的序列名称。如果没有序列则为空。
     */
    public $sequenceName;
    /**
     * @var array 表的外键。每个数组元素具有以下结构：
     *
     * ```php
     * [
     *  'ForeignTableName',
     *  'fk1' => 'pk1',  // pk1 is in foreign table
     *  'fk2' => 'pk2',  // if composite foreign key
     * ]
     * ```
     */
    public $foreignKeys = [];
    /**
     * @var ColumnSchema[] 表的列元数据。每个元素都是 [[ColumnSchema]] 对象，由列名索引。
     */
    public $columns = [];


    /**
     * 获取指定的列元数据。
     * 这是一种检索命名列的便捷方法，即使它不存在也是如此。
     * @param string $name 列名
     * @return ColumnSchema 指定列的元数据。如果指定列不存在，则为空。
     */
    public function getColumn($name)
    {
        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    /**
     * 返回此表中所有列的名称。
     * @return array 列名列表
     */
    public function getColumnNames()
    {
        return array_keys($this->columns);
    }

    /**
     * 手动指定此表的主键。
     * @param string|array $keys 主键（可以是复合键）
     * @throws InvalidArgumentException 如果在表中找不到指定的键抛出的异常。
     */
    public function fixPrimaryKey($keys)
    {
        $keys = (array) $keys;
        $this->primaryKey = $keys;
        foreach ($this->columns as $column) {
            $column->isPrimaryKey = false;
        }
        foreach ($keys as $key) {
            if (isset($this->columns[$key])) {
                $this->columns[$key]->isPrimaryKey = true;
            } else {
                throw new InvalidArgumentException("Primary key '$key' cannot be found in table '{$this->name}'.");
            }
        }
    }
}
