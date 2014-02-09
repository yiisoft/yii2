<?php

namespace yii\mq\db\models;

use yii\mq\Message;

/**
 * Collection of scopes for the DbMessage AR model.
 */
class DbMessageQuery extends \yii\db\ActiveQuery
{
	/**
	 * @return DbMessageQuery $this
	 */
	public function deleted()
	{
		$modelClass = $this->modelClass;
		$this->andWhere($modelClass::tableName().'.status = '.Message::DELETED);
		return $this;
	}

	/**
	 * @param integer $timeout
	 * @return DbMessageQuery $this
	 */
	public function available($timeout=null)
	{
		return self::withStatus(Message::AVAILABLE, $timeout);
	}

	/**
	 * @param integer $timeout
	 * @return DbMessageQuery $this
	 */
	public function reserved($timeout=null)
	{
		return self::withStatus(Message::RESERVED, $timeout);
	}

	/**
	 * @param integer $timeout
	 * @return DbMessageQuery $this
	 */
	public function timedout($timeout=null)
	{
		if ($timeout === null) {
			$this->andWhere('1=0');
			return $this;
		}
		$now = new \DateTime($timeout === null ? '' : "-$timeout seconds", new \DateTimezone('UTC'));
		$modelClass = $this->modelClass;
        $t = $modelClass::tableName();
		$this->andWhere("($t.status=".Message::RESERVED." AND $t.reserved_on <= :timeout)", [':timeout'=>$now->format('Y-m-d H:i:s')]);
		return $this;
	}

	/**
	 * @param array|string $statuses
	 * @param integer $timeout
	 * @return DbMessageQuery $this
	 */
	public function withStatus($statuses, $timeout=null)
	{
		if (!is_array($statuses))
			$statuses = [$statuses];
		$modelClass = $this->modelClass;
        $t = $modelClass::tableName();
		$now = new \DateTime($timeout === null ? '' : "-$timeout seconds", new \DateTimezone('UTC'));
		$conditions = ['or'];
		// test for two special cases
		if (array_diff($statuses, [Message::AVAILABLE, Message::RESERVED]) === []) {
			// only not deleted
			$conditions[] = "$t.status!=".Message::DELETED;
		} elseif (array_diff($statuses, [Message::AVAILABLE, Message::RESERVED, Message::DELETED]) === []) {
			// pass - don't add no conditions
		} else {
			// merge all statuses
			foreach($statuses as $status) {
				switch($status) {
					case Message::AVAILABLE:
						$conditions[] = "$t.status=".$status;
						if ($timeout !== null) {
							$conditions[] = "($t.status=".Message::RESERVED." AND $t.reserved_on <= :timeout)";
							$this->addParams([':timeout'=>$now->format('Y-m-d H:i:s')]);
						}
						break;
					case Message::RESERVED:
						if ($timeout !== null) {
							$conditions[] = "($t.status=$status AND $t.reserved_on > :timeout)";
							$this->addParams([':timeout'=>$now->format('Y-m-d H:i:s')]);
						} else {
							$conditions[] = "$t.status=".$status;
						}
						break;
					case Message::DELETED:
						$conditions[] = "$t.status=".$status;
						break;
				}
			}
		}
		if ($conditions !== ['or']) {
			$this->andWhere($conditions);
		}
		return $this;
	}

	/**
	 * @param string $queue_id
	 * @return DbMessageQuery $this
	 */
	public function withQueue($queue_id)
	{
		$modelClass = $this->modelClass;
        $t = $modelClass::tableName();
		$pk = $modelClass::primaryKey();
		$this->andWhere($t.'.queue_id=:queue_id', [':queue_id'=>$queue_id]);
		$this->orderBy = ["$t.{$pk[0]}"=>'ASC'];
		return $this;
	}

	/**
	 * @param string $subscriber_id
	 * @return DbMessageQuery $this
	 */
	public function withSubscriber($subscriber_id=null)
	{
		if ($subscriber_id === null) {
			$modelClass = $this->modelClass;
			$t = $modelClass::tableName();
			$this->andWhere("$t.subscription_id IS NULL");
		} else {
			$this->innerJoinWith('subscription');
			$this->andWhere(DbSubscription::tableName().'.subscriber_id=:subscriber_id', [':subscriber_id'=>$subscriber_id]);
		}
		return $this;
	}
}
