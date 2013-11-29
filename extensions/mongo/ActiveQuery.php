<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;

/**
 * Class ActiveQuery
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveQuery extends Query implements ActiveQueryInterface
{
	use ActiveQueryTrait;

	/**
	 * Executes query and returns all results as an array.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all($db = null)
	{
		$cursor = $this->buildCursor($db);
		$rows = [];
		foreach ($cursor as $row) {
			$rows[] = $row;
		}
		if (!empty($rows)) {
			$models = $this->createModels($rows);
			if (!empty($this->with)) {
				$this->findWith($this->with, $models);
			}
			return $models;
		} else {
			return [];
		}
	}

	/**
	 * Executes query and returns a single row of result.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
	 * the query result may be either an array or an ActiveRecord object. Null will be returned
	 * if the query results in nothing.
	 */
	public function one($db = null)
	{
		$row = parent::one($db);
		if ($row !== false) {
			if ($this->asArray) {
				$model = $row;
			} else {
				/** @var ActiveRecord $class */
				$class = $this->modelClass;
				$model = $class::create($row);
			}
			if (!empty($this->with)) {
				$models = [$model];
				$this->findWith($this->with, $models);
				$model = $models[0];
			}
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * Returns the Mongo collection for this query.
	 * @param Connection $db Mongo connection.
	 * @return Collection collection instance.
	 */
	public function getCollection($db = null)
	{
		/** @var ActiveRecord $modelClass */
		$modelClass = $this->modelClass;
		if ($db === null) {
			$db = $modelClass::getDb();
		}
		if ($this->from === null) {
			$this->from = $modelClass::collectionName();
		}
		return $db->getCollection($this->from);
	}
}