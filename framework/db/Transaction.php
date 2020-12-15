<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * Transaction represents a DB transaction.
 *
 * It is usually created by calling [[Connection::beginTransaction()]].
 *
 * The following code is a typical example of using transactions (note that some
 * DBMS may not support transactions):
 *
 * ```php
 * $transaction = $connection->beginTransaction();
 * try {
 *     $connection->createCommand($sql1)->execute();
 *     $connection->createCommand($sql2)->execute();
 *     //.... other SQL executions
 *     $transaction->commit();
 * } catch (\Exception $e) {
 *     $transaction->rollBack();
 *     throw $e;
 * } catch (\Throwable $e) {
 *     $transaction->rollBack();
 *     throw $e;
 * }
 * ```
 *
 * > Note: in the above code we have two catch-blocks for compatibility
 * > with PHP 5.x and PHP 7.x. `\Exception` implements the [`\Throwable` interface](https://secure.php.net/manual/en/class.throwable.php)
 * > since PHP 7.0, so you can skip the part with `\Exception` if your app uses only PHP 7.0 and higher.
 *
 * @property-read bool $isActive Whether this transaction is active. Only an active transaction can
 * [[commit()]] or [[rollBack()]]. This property is read-only.
 * @property-write string $isolationLevel The transaction isolation level to use for this transaction. This
 * can be one of [[READ_UNCOMMITTED]], [[READ_COMMITTED]], [[REPEATABLE_READ]] and [[SERIALIZABLE]] but also a
 * string containing DBMS specific syntax to be used after `SET TRANSACTION ISOLATION LEVEL`. This property is
 * write-only.
 * @property-read int $level The current nesting level of the transaction. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Transaction extends \yii\base\BaseObject
{
    /**
     * A constant representing the transaction isolation level `READ UNCOMMITTED`.
     * @see http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
     */
    const READ_UNCOMMITTED = 'READ UNCOMMITTED';
    /**
     * A constant representing the transaction isolation level `READ COMMITTED`.
     * @see http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
     */
    const READ_COMMITTED = 'READ COMMITTED';
    /**
     * A constant representing the transaction isolation level `REPEATABLE READ`.
     * @see http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
     */
    const REPEATABLE_READ = 'REPEATABLE READ';
    /**
     * A constant representing the transaction isolation level `SERIALIZABLE`.
     * @see http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
     */
    const SERIALIZABLE = 'SERIALIZABLE';

    /**
     * @var Connection the database connection that this transaction is associated with.
     */
    public $db;

    /**
     * @var int the nesting level of the transaction. 0 means the outermost level.
     */
    private $_level = 0;


    /**
     * Returns a value indicating whether this transaction is active.
     * @return bool whether this transaction is active. Only an active transaction
     * can [[commit()]] or [[rollBack()]].
     */
    public function getIsActive()
    {
        return $this->_level > 0 && $this->db && $this->db->isActive;
    }

    /**
     * Begins a transaction.
     * @param string|null $isolationLevel The [isolation level][] to use for this transaction.
     * This can be one of [[READ_UNCOMMITTED]], [[READ_COMMITTED]], [[REPEATABLE_READ]] and [[SERIALIZABLE]] but
     * also a string containing DBMS specific syntax to be used after `SET TRANSACTION ISOLATION LEVEL`.
     * If not specified (`null`) the isolation level will not be set explicitly and the DBMS default will be used.
     *
     * > Note: This setting does not work for PostgreSQL, where setting the isolation level before the transaction
     * has no effect. You have to call [[setIsolationLevel()]] in this case after the transaction has started.
     *
     * > Note: Some DBMS allow setting of the isolation level only for the whole connection so subsequent transactions
     * may get the same isolation level even if you did not specify any. When using this feature
     * you may need to set the isolation level for all transactions explicitly to avoid conflicting settings.
     * At the time of this writing affected DBMS are MSSQL and SQLite.
     *
     * [isolation level]: http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
     *
     * Starting from version 2.0.16, this method throws exception when beginning nested transaction and underlying DBMS
     * does not support savepoints.
     * @throws InvalidConfigException if [[db]] is `null`
     * @throws NotSupportedException if the DBMS does not support nested transactions
     * @throws Exception if DB connection fails
     */
    public function begin($isolationLevel = null)
    {
        if ($this->db === null) {
            throw new InvalidConfigException('Transaction::db must be set.');
        }
        $this->db->open();

        if ($this->_level === 0) {
            if ($isolationLevel !== null) {
                $this->db->getSchema()->setTransactionIsolationLevel($isolationLevel);
            }
            Yii::debug('Begin transaction' . ($isolationLevel ? ' with isolation level ' . $isolationLevel : ''), __METHOD__);

            $this->db->trigger(Connection::EVENT_BEGIN_TRANSACTION);
            $this->db->pdo->beginTransaction();
            $this->_level = 1;

            return;
        }

        $schema = $this->db->getSchema();
        if ($schema->supportsSavepoint()) {
            Yii::debug('Set savepoint ' . $this->_level, __METHOD__);
            // make sure the transaction wasn't autocommitted
            if ($this->db->pdo->inTransaction()) {
                $schema->createSavepoint('LEVEL' . $this->_level);
            }
        } else {
            Yii::info('Transaction not started: nested transaction not supported', __METHOD__);
            throw new NotSupportedException('Transaction not started: nested transaction not supported.');
        }
        $this->_level++;
    }

    /**
     * Commits a transaction.
     * @throws Exception if the transaction is not active
     */
    public function commit()
    {
        if (!$this->getIsActive()) {
            throw new Exception('Failed to commit transaction: transaction was inactive.');
        }

        $this->_level--;
        if ($this->_level === 0) {
            Yii::debug('Commit transaction', __METHOD__);
            // make sure the transaction wasn't autocommitted
            if ($this->db->pdo->inTransaction()) {
                $this->db->pdo->commit();
            }
            $this->db->trigger(Connection::EVENT_COMMIT_TRANSACTION);
            return;
        }

        $schema = $this->db->getSchema();
        if ($schema->supportsSavepoint()) {
            Yii::debug('Release savepoint ' . $this->_level, __METHOD__);
            // make sure the transaction wasn't autocommitted
            if ($this->db->pdo->inTransaction()) {
                $schema->releaseSavepoint('LEVEL' . $this->_level);
            }
        } else {
            Yii::info('Transaction not committed: nested transaction not supported', __METHOD__);
        }
    }

    /**
     * Rolls back a transaction.
     */
    public function rollBack()
    {
        if (!$this->getIsActive()) {
            // do nothing if transaction is not active: this could be the transaction is committed
            // but the event handler to "commitTransaction" throw an exception
            return;
        }

        $this->_level--;
        if ($this->_level === 0) {
            Yii::debug('Roll back transaction', __METHOD__);
            // make sure the transaction wasn't autocommitted
            if ($this->db->pdo->inTransaction()) {
                $this->db->pdo->rollBack();
            }
            $this->db->trigger(Connection::EVENT_ROLLBACK_TRANSACTION);
            return;
        }

        $schema = $this->db->getSchema();
        if ($schema->supportsSavepoint()) {
            Yii::debug('Roll back to savepoint ' . $this->_level, __METHOD__);
            // make sure the transaction wasn't autocommitted
            if ($this->db->pdo->inTransaction()) {
                $schema->rollBackSavepoint('LEVEL' . $this->_level);
            }
        } else {
            Yii::info('Transaction not rolled back: nested transaction not supported', __METHOD__);
        }
    }

    /**
     * Sets the transaction isolation level for this transaction.
     *
     * This method can be used to set the isolation level while the transaction is already active.
     * However this is not supported by all DBMS so you might rather specify the isolation level directly
     * when calling [[begin()]].
     * @param string $level The transaction isolation level to use for this transaction.
     * This can be one of [[READ_UNCOMMITTED]], [[READ_COMMITTED]], [[REPEATABLE_READ]] and [[SERIALIZABLE]] but
     * also a string containing DBMS specific syntax to be used after `SET TRANSACTION ISOLATION LEVEL`.
     * @throws Exception if the transaction is not active
     * @see http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
     */
    public function setIsolationLevel($level)
    {
        if (!$this->getIsActive()) {
            throw new Exception('Failed to set isolation level: transaction was inactive.');
        }
        Yii::debug('Setting transaction isolation level to ' . $level, __METHOD__);
        $this->db->getSchema()->setTransactionIsolationLevel($level);
    }

    /**
     * @return int The current nesting level of the transaction.
     * @since 2.0.8
     */
    public function getLevel()
    {
        return $this->_level;
    }
}
