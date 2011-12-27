<?php
/**
 * Transaction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao;

use yii\db\Exception;

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
 *	 $connection->createCommand($sql1)->execute();
 *	 $connection->createCommand($sql2)->execute();
 *	 //.... other SQL executions
 *	 $transaction->commit();
 * } catch(Exception $e) {
 *	 $transaction->rollBack();
 * }
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Transaction extends \yii\base\Object
{
	/**
	 * @var boolean whether this transaction is active. Only an active transaction
	 * can [[commit()]] or [[rollBack()]]. This property is set true when the transaction is started.
	 */
	public $active;
	/**
	 * @var Connection the database connection that this transaction is associated with.
	 */
	public $connection;

	/**
	 * Constructor.
	 * @param Connection $connection the connection associated with this transaction
	 * @see Connection::beginTransaction
	 */
	public function __construct($connection)
	{
		$this->active = true;
		$this->connection = $connection;
	}

	/**
	 * Commits a transaction.
	 * @throws Exception if the transaction or the DB connection is not active.
	 */
	public function commit()
	{
		if ($this->active && $this->connection->getActive()) {
			\Yii::trace('Committing transaction', __CLASS__);
			$this->connection->pdo->commit();
			$this->active = false;
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
		if ($this->active && $this->connection->getActive()) {
			\Yii::trace('Rolling back transaction', __CLASS__);
			$this->connection->pdo->rollBack();
			$this->active = false;
		} else {
			throw new Exception('Failed to roll back transaction: transaction was inactive.');
		}
	}
}
