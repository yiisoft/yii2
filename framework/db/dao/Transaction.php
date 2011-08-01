<?php
/**
 * CDbTransaction class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbTransaction represents a DB transaction.
 *
 * It is usually created by calling {@link CDbConnection::beginTransaction}.
 *
 * The following code is a common scenario of using transactions:
 * <pre>
 * $transaction=$connection->beginTransaction();
 * try
 * {
 *    $connection->createCommand($sql1)->execute();
 *    $connection->createCommand($sql2)->execute();
 *    //.... other SQL executions
 *    $transaction->commit();
 * }
 * catch(Exception $e)
 * {
 *    $transaction->rollBack();
 * }
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbTransaction.php 3069 2011-03-14 00:28:38Z qiang.xue $
 * @package system.db
 * @since 1.0
 */
class CDbTransaction extends CComponent
{
	private $_connection = null;
	private $_active;

	/**
	 * Constructor.
	 * @param CDbConnection $connection the connection associated with this transaction
	 * @see CDbConnection::beginTransaction
	 */
	public function __construct(CDbConnection $connection)
	{
		$this->_connection = $connection;
		$this->_active = true;
	}

	/**
	 * Commits a transaction.
	 * @throws CException if the transaction or the DB connection is not active.
	 */
	public function commit()
	{
		if ($this->_active && $this->_connection->getActive())
		{
			Yii::trace('Committing transaction', 'system.db.CDbTransaction');
			$this->_connection->getPdoInstance()->commit();
			$this->_active = false;
		}
		else
			throw new CDbException(Yii::t('yii', 'CDbTransaction is inactive and cannot perform commit or roll back operations.'));
	}

	/**
	 * Rolls back a transaction.
	 * @throws CException if the transaction or the DB connection is not active.
	 */
	public function rollback()
	{
		if ($this->_active && $this->_connection->getActive())
		{
			Yii::trace('Rolling back transaction', 'system.db.CDbTransaction');
			$this->_connection->getPdoInstance()->rollBack();
			$this->_active = false;
		}
		else
			throw new CDbException(Yii::t('yii', 'CDbTransaction is inactive and cannot perform commit or roll back operations.'));
	}

	/**
	 * @return CDbConnection the DB connection for this transaction
	 */
	public function getConnection()
	{
		return $this->_connection;
	}

	/**
	 * @return boolean whether this transaction is active
	 */
	public function getActive()
	{
		return $this->_active;
	}

	/**
	 * @param boolean $value whether this transaction is active
	 */
	protected function setActive($value)
	{
		$this->_active = $value;
	}
}
