<?php
/**
 * Transaction class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\BadConfigException;

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
 * } catch(Exception $e) {
 *     $transaction->rollBack();
 * }
 * ~~~
 *
 * @property boolean $isActive Whether the transaction is active. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Transaction extends \yii\base\Object
{
	/**
	 * @var Connection the database connection that this transaction is associated with.
	 */
	public $connection;
	/**
	 * @var boolean whether this transaction is active. Only an active transaction
	 * can [[commit()]] or [[rollBack()]]. This property is set true when the transaction is started.
	 */
	private $_active = false;

	/**
	 * Returns a value indicating whether this transaction is active.
	 * @return boolean whether this transaction is active. Only an active transaction
	 * can [[commit()]] or [[rollBack()]].
	 */
	public function getIsActive()
	{
		return $this->_active;
	}

	/**
	 * Begins a transaction.
	 * @throws BadConfigException if [[connection]] is null
	 */
	public function begin()
	{
		if (!$this->_active) {
			if ($this->connection === null) {
				throw new BadConfigException('Transaction.connection must be set.');
			}
			\Yii::trace('Starting transaction', __CLASS__);
			$this->connection->open();
			$this->connection->pdo->beginTransaction();
			$this->_active = true;
		}
	}

	/**
	 * Commits a transaction.
	 * @throws Exception if the transaction or the DB connection is not active.
	 */
	public function commit()
	{
		if ($this->_active && $this->connection && $this->connection->isActive) {
			\Yii::trace('Committing transaction', __CLASS__);
			$this->connection->pdo->commit();
			$this->_active = false;
		} else {
			throw new Exception('Failed to commit transaction: transaction was inactive.');
		}
	}

	/**
	 * Rolls back a transaction.
	 * @throws Exception if the transaction or the DB connection is not active.
	 */
	public function rollback()
	{
		if ($this->_active && $this->connection && $this->connection->isActive) {
			\Yii::trace('Rolling back transaction', __CLASS__);
			$this->connection->pdo->commit();
			$this->_active = false;
		} else {
			throw new Exception('Failed to roll back transaction: transaction was inactive.');
		}
	}
}
