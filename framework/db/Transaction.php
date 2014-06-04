<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Transaction represents a DB transaction.
 *
 * It is usually created by calling [[Connection::beginTransaction()]].
 *
 * The following code is a typical example of using transactions (note that some
 * DBMS may not support transactions):
 *
 * ~~~
 * $transaction = $connection->beginTransaction();
 * try {
 *     $connection->createCommand($sql1)->execute();
 *     $connection->createCommand($sql2)->execute();
 *     //.... other SQL executions
 *     $transaction->commit();
 * } catch (Exception $e) {
 *     $transaction->rollBack();
 * }
 * ~~~
 *
 * @property boolean $isActive Whether this transaction is active. Only an active transaction can [[commit()]]
 * or [[rollBack()]]. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Transaction extends \yii\base\Object
{
    /**
     * @var Connection the database connection that this transaction is associated with.
     */
    public $db;
    /**
     * @var integer the nesting level of the transaction. 0 means the outermost level.
     */
    private $_level = 0;

    /**
     * Returns a value indicating whether this transaction is active.
     * @return boolean whether this transaction is active. Only an active transaction
     * can [[commit()]] or [[rollBack()]].
     */
    public function getIsActive()
    {
        return $this->_level > 0 && $this->db && $this->db->isActive;
    }

    /**
     * Begins a transaction.
     * @throws InvalidConfigException if [[db]] is `null`.
     */
    public function begin()
    {
        if ($this->db === null) {
            throw new InvalidConfigException('Transaction::db must be set.');
        }
        $this->db->open();

        if ($this->_level == 0) {
            Yii::trace('Begin transaction', __METHOD__);
            $this->db->pdo->beginTransaction();
            $this->_level = 1;

            return;
        }

        $schema = $this->db->getSchema();
        if ($schema->supportsSavepoint()) {
            Yii::trace('Set savepoint ' . $this->_level, __METHOD__);
            $schema->createSavepoint('LEVEL' . $this->_level);
        } else {
            Yii::info('Transaction not started: nested transaction not supported', __METHOD__);
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
        if ($this->_level == 0) {
            Yii::trace('Commit transaction', __METHOD__);
            $this->db->pdo->commit();

            return;
        }

        $schema = $this->db->getSchema();
        if ($schema->supportsSavepoint()) {
            Yii::trace('Release savepoint ' . $this->_level, __METHOD__);
            $schema->releaseSavepoint('LEVEL' . $this->_level);
        } else {
            Yii::info('Transaction not committed: nested transaction not supported', __METHOD__);
        }
    }

    /**
     * Rolls back a transaction.
     * @throws Exception if the transaction is not active
     */
    public function rollBack()
    {
        if (!$this->getIsActive()) {
            throw new Exception('Failed to roll back transaction: transaction was inactive.');
        }

        $this->_level--;
        if ($this->_level == 0) {
            Yii::trace('Roll back transaction', __METHOD__);
            $this->db->pdo->rollBack();

            return;
        }

        $schema = $this->db->getSchema();
        if ($schema->supportsSavepoint()) {
            Yii::trace('Roll back to savepoint ' . $this->_level, __METHOD__);
            $schema->rollBackSavepoint('LEVEL' . $this->_level);
        } else {
            Yii::info('Transaction not rolled back: nested transaction not supported', __METHOD__);
            // throw an exception to fail the outer transaction
            throw new Exception('Roll back failed: nested transaction not supported.');
        }
    }
}
