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
	 * @return DbMessageQuery $this
	 */
	public function available()
	{
		return self::withStatus(Message::AVAILABLE);
	}

	/**
	 * @return DbMessageQuery $this
	 */
	public function reserved()
	{
		return self::withStatus(Message::RESERVED);
	}

	/**
	 * @return DbMessageQuery $this
	 */
	public function timedout()
	{
		$now = new \DateTime('', new \DateTimezone('UTC'));
		$modelClass = $this->modelClass;
        $t = $modelClass::tableName();
		$this->andWhere("($t.status=".Message::RESERVED." AND $t.times_out_on > :timeout)", [':timeout'=>$now->format('Y-m-d H:i:s')]);
		return $this;
	}

	/**
	 * @param array|string $statuses
	 * @return DbMessageQuery $this
	 */
	public function withStatus($statuses)
	{
		if (!is_array($statuses))
			$statuses = [$statuses];
		$modelClass = $this->modelClass;
        $t = $modelClass::tableName();
		$now = new \DateTime('', new \DateTimezone('UTC'));
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
						$conditions[] = "($t.status=".Message::RESERVED." AND $t.times_out_on > :timeout)";
						$this->addParams([':timeout'=>$now->format('Y-m-d H:i:s')]);
						break;
					case Message::RESERVED:
						$conditions[] = "($t.status=$status AND $t.times_out_on <= :timeout)";
						$this->addParams([':timeout'=>$now->format('Y-m-d H:i:s')]);
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
