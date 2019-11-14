<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

/**
 * MSSQL 和 DBLIB 驱动默认的 PDO 类扩展。
 * 该扩展为 MSSQL 和 DBLIB 驱动的不能正确实现的功能提供了变通方法。
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class PDO extends \PDO
{
    /**
     * 返回最后插入的 ID 的值。
     * @param string|null $sequence 序列名。默认为空。
     * @return int 最后插入的 ID 的值。
     */
    public function lastInsertId($sequence = null)
    {
        return $this->query('SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS bigint)')->fetchColumn();
    }

    /**
     * 开始事务方法。因为 MSSQL PDO 驱动本身不支持事务，
     * 所以有必要覆盖 PDO 类的方法。
     * @return bool 开始事务的结果。
     */
    public function beginTransaction()
    {
        $this->exec('BEGIN TRANSACTION');

        return true;
    }

    /**
     * 提交事务方法。因为 MSSQL PDO 驱动本身不支持事务，
     * 所以有必要覆盖 PDO 类的方法。
     * @return bool 提交事务的结果。
     */
    public function commit()
    {
        $this->exec('COMMIT TRANSACTION');

        return true;
    }

    /**
     * 回滚事务方法。因为 MSSQL PDO 驱动本身不支持事务，
     * 所以有必要覆盖 PDO 类的方法。
     * @return bool 回滚事务的结果。
     */
    public function rollBack()
    {
        $this->exec('ROLLBACK TRANSACTION');

        return true;
    }

    /**
     * 检索数据库连接属性。
     *
     * 因为某些 MSSQL PDO 驱动（例如：dblib）不支持获取连接属性，
     * 所以有必要覆盖 PDO 类的方法。
     * @param int $attribute PDO::ATTR_* 常量之一。
     * @return mixed 调用成功后将返回所请求的 PDO 属性的值。
     * 调用不成功，则返回 null。
     */
    public function getAttribute($attribute)
    {
        try {
            return parent::getAttribute($attribute);
        } catch (\PDOException $e) {
            switch ($attribute) {
                case self::ATTR_SERVER_VERSION:
                    return $this->query("SELECT CAST(SERVERPROPERTY('productversion') AS VARCHAR)")->fetchColumn();
                default:
                    throw $e;
            }
        }
    }
}
