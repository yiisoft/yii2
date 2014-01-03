<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\db\Query;

/**
 * DbSessionHandler implements SessionHandlerInterface and provides a database session data storage.
 *
 * DbSessionHandler is used by DbSession.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbSessionHandler extends SessionHandler
{
	/**
	 * @inheritdoc
	 */
	public function read($id)
	{
		$query = new Query;
		$data = $query->select(['data'])
			->from($this->owner->sessionTable)
			->where('[[expire]]>:expire AND [[id]]=:id', [':expire' => time(), ':id' => $id])
			->createCommand($this->owner->db)
			->queryScalar();
		return $data === false ? '' : $data;
	}

	/**
	 * @inheritdoc
	 */
	public function write($id, $data)
	{
		// exception must be caught in session write handler
		// http://us.php.net/manual/en/function.session-set-save-handler.php
		try {
			$expire = time() + $this->owner->getTimeout();
			$query = new Query;
			$exists = $query->select(['id'])
				->from($this->owner->sessionTable)
				->where(['id' => $id])
				->createCommand($this->owner->db)
				->queryScalar();
			if ($exists === false) {
				$this->owner->db->createCommand()
					->insert($this->owner->sessionTable, [
						'id' => $id,
						'data' => $data,
						'expire' => $expire,
					])->execute();
			} else {
				$this->owner->db->createCommand()
					->update($this->owner->sessionTable, ['data' => $data, 'expire' => $expire], ['id' => $id])
					->execute();
			}
		} catch (\Exception $e) {
			if (YII_DEBUG) {
				echo $e->getMessage();
			}
			// it is too late to log an error message here
			return false;
		}
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function destroy($id)
	{
		$this->owner->db->createCommand()
			->delete($this->owner->sessionTable, ['id' => $id])
			->execute();
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function gc($maxLifetime)
	{
		$this->owner->db->createCommand()
			->delete($this->owner->sessionTable, '[[expire]]<:expire', [':expire' => time()])
			->execute();
		return true;
	}
}
