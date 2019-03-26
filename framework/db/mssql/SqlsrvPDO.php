<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

/**
 * SQLSRV 驱动的默认 PDO 类扩展。
 * 为 SQLSRV 驱动不能正确实现的功能提供了变通方法。
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class SqlsrvPDO extends \PDO
{
    /**
     * 返回最后插入的 ID 的值。
     *
     * SQLSRV 驱动实现了 [[PDO::lastInsertId()]] 方法，但具有单一的特性：
     * 当 `$sequence` 值为 null 或空字符串时，返回一个空字符串。
     * 但是，当没有指定参数时，它将按预期工作，
     * 并返回实际的最后插入的 ID（与其他 PDO 驱动程序一样）。
     * @param string|null $sequence 序列名称。默认为空。
     * @return int 最后插入的 ID 的值。
     */
    public function lastInsertId($sequence = null)
    {
        return !$sequence ? parent::lastInsertId() : parent::lastInsertId($sequence);
    }
}
