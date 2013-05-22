<?php
/**
 * Transaction class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\redis;

use yii\base\InvalidConfigException;
use yii\db\Exception;

/**
 * Transaction represents a DB transaction.
 *
 * @property boolean $isActive Whether the transaction is active. This property is read-only.
 *
 * @since 2.0
 */
class Transaction extends \yii\base\Object
{
	/**
	 * @var Connection the database connection that this transaction is associated with.
	 */
	public $db;
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
	 * @throws InvalidConfigException if [[connection]] is null
	 */
	public function begin()
	{
		if (!$this->_active) {
			if ($this->db === null) {
				throw new InvalidConfigException('Transaction::db must be set.');
			}
			\Yii::trace('Starting transaction', __CLASS__);
			$this->db->open();
			$this->db->createCommand('MULTI')->execute();
			$this->_active = true;
		}
	}

	/**
	 * Commits a transaction.
	 * @throws Exception if the transaction or the DB connection is not active.
	 */
	public function commit()
	{
		if ($this->_active && $this->db && $this->db->isActive) {
			\Yii::trace('Committing transaction', __CLASS__);
			$this->db->createCommand('EXEC')->execute();
			// TODO handle result of EXEC
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
		if ($this->_active && $this->db && $this->db->isActive) {
			\Yii::trace('Rolling back transaction', __CLASS__);
			$this->db->pdo->commit();
			$this->_active = false;
		} else {
			throw new Exception('Failed to roll back transaction: transaction was inactive.');
		}
	}
}
