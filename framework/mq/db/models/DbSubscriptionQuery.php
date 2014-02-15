<?php

namespace yii\mq\db\models;

/**
 * Collection of scopes for the DbSubscription AR model.
 */
class DbSubscriptionQuery extends \yii\db\ActiveQuery
{
	public function current()
	{
		$modelClass = $this->modelClass;
		$this->andWhere($modelClass::tableName().'.is_deleted = 0');
		return $this;
	}

	/**
	 * @param string $queue_id
	 * @return DbSubscriptionQuery $this
	 */
	public function withQueue($queue_id)
	{
		$modelClass = $this->modelClass;
        $this->andWhere($modelClass::tableName().'.queue_id=:queue_id', [':queue_id'=>$queue_id]);
		return $this;
	}

	/**
	 * @param string $subscriber_id
	 * @return DbSubscriptionQuery $this
	 */
	public function withSubscriber($subscriber_id)
	{
		$modelClass = $this->modelClass;
        $this->andWhere($modelClass::tableName().'.subscriber_id=:subscriber_id', [':subscriber_id'=>$subscriber_id]);
		return $this;
	}

	/**
	 * @param array|string $categories
	 * @return DbSubscriptionQuery $this
	 */
	public function matchingCategory($categories)
	{
        if ($categories===null)
            return $this;
		$r = DbSubscriptionCategory::tableName();

        if (!is_array($categories))
            $categories = [$categories];

		$this->innerJoinWith('categories');

        $i = 0;
        foreach($categories as $category) {
			$this->andWhere("($r.is_exception = 0 AND :category$i LIKE $r.category) OR ($r.is_exception = 1 AND :category$i NOT LIKE $r.category)", [':category'.$i++ => $category]);
        }
		return $this;
	}
}
