<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb\file;

use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;
use yii\db\ActiveRelationInterface;
use yii\db\ActiveRelationTrait;

/**
 * ActiveQuery represents a Mongo query associated with an file Active Record class.
 *
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]].
 *
 * Because ActiveQuery extends from [[Query]], one can use query methods, such as [[where()]],
 * [[orderBy()]] to customize the query options.
 *
 * ActiveQuery also provides the following additional query options:
 *
 * - [[with()]]: list of relations that this query should be performed with.
 * - [[asArray()]]: whether to return each record as an array.
 *
 * These options can be configured using methods of the same name. For example:
 *
 * ~~~
 * $images = ImageFile::find()->with('tags')->asArray()->all();
 * ~~~
 *
 * @property Collection $collection Collection instance. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveQuery extends Query implements ActiveQueryInterface, ActiveRelationInterface
{
	use ActiveQueryTrait;
	use ActiveRelationTrait;

	/**
	 * Executes query and returns all results as an array.
	 * @param \yii\mongodb\Connection $db the Mongo connection used to execute the query.
	 * If null, the Mongo connection returned by [[modelClass]] will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all($db = null)
	{
		$cursor = $this->buildCursor($db);
		$rows = $this->fetchRows($cursor);
		if (!empty($rows)) {
			$models = $this->createModels($rows);
			if (!empty($this->with)) {
				$this->findWith($this->with, $models);
			}
			if (!$this->asArray) {
				foreach($models as $model) {
					$model->afterFind();
				}
			}
			return $models;
		} else {
			return [];
		}
	}

	/**
	 * Executes query and returns a single row of result.
	 * @param \yii\mongodb\Connection $db the Mongo connection used to execute the query.
	 * If null, the Mongo connection returned by [[modelClass]] will be used.
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
				$model = $class::instantiate($row);
				$class::populateRecord($model, $row);
			}
			if (!empty($this->with)) {
				$models = [$model];
				$this->findWith($this->with, $models);
				$model = $models[0];
			}
			if (!$this->asArray) {
				$model->afterFind();
			}
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * Returns the Mongo collection for this query.
	 * @param \yii\mongodb\Connection $db Mongo connection.
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
		return $db->getFileCollection($this->from);
	}
}