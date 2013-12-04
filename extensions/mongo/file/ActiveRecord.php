<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo\file;

/**
 * ActiveRecord is the base class for classes representing Mongo GridFS files in terms of objects.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveRecord extends \yii\mongo\ActiveRecord
{
	/**
	 * @var \MongoGridFSFile|string
	 */
	public $file;

	/**
	 * Creates an [[ActiveQuery]] instance.
	 * This method is called by [[find()]] to start a "find" command.
	 * You may override this method to return a customized query (e.g. `CustomerQuery` specified
	 * written for querying `Customer` purpose.)
	 * @return ActiveQuery the newly created [[ActiveQuery]] instance.
	 */
	public static function createQuery()
	{
		return new ActiveQuery(['modelClass' => get_called_class()]);
	}

	/**
	 * Return the Mongo GridFS collection instance for this AR class.
	 * @return Collection collection instance.
	 */
	public static function getCollection()
	{
		return static::getDb()->getFileCollection(static::collectionName());
	}

	/**
	 * Creates an [[ActiveRelation]] instance.
	 * This method is called by [[hasOne()]] and [[hasMany()]] to create a relation instance.
	 * You may override this method to return a customized relation.
	 * @param array $config the configuration passed to the ActiveRelation class.
	 * @return ActiveRelation the newly created [[ActiveRelation]] instance.
	 */
	public static function createActiveRelation($config = [])
	{
		return new ActiveRelation($config);
	}

	/**
	 * Creates an active record object using a row of data.
	 * This method is called by [[ActiveQuery]] to populate the query results
	 * into Active Records. It is not meant to be used to create new records.
	 * @param \MongoGridFSFile $row attribute values (name => value)
	 * @return ActiveRecord the newly created active record.
	 */
	public static function create($row)
	{
		$record = static::instantiate($row);
		$columns = array_flip($record->attributes());
		foreach ($row->file as $name => $value) {
			if (isset($columns[$name])) {
				$record->setAttribute($name, $value);
			} else {
				$record->$name = $value;
			}
		}
		$record->setOldAttributes($record->getAttributes());
		$record->afterFind();
		return $record;
	}

	/**
	 * Returns the list of all attribute names of the model.
	 * This method could be overridden by child classes to define available attributes.
	 * Note: primary key attribute "_id" should be always present in returned array.
	 * @return array list of attribute names.
	 */
	public function attributes()
	{
		return ['id', 'filename'];
	}

	/**
	 * @see ActiveRecord::insert()
	 */
	protected function insertInternal($attributes = null)
	{
		if (!$this->beforeSave(true)) {
			return false;
		}
		$values = $this->getDirtyAttributes($attributes);
		if (empty($values)) {
			$currentAttributes = $this->getAttributes();
			foreach ($this->primaryKey() as $key) {
				$values[$key] = isset($currentAttributes[$key]) ? $currentAttributes[$key] : null;
			}
		}
		$collection = static::getCollection();
		$newId = $collection->insert($values);
		$this->setAttribute('_id', $newId);
		foreach ($values as $name => $value) {
			$this->setOldAttribute($name, $value);
		}
		$this->afterSave(true);
		return true;
	}

	/**
	 * @see CActiveRecord::update()
	 * @throws StaleObjectException
	 */
	protected function updateInternal($attributes = null)
	{
		if (!$this->beforeSave(false)) {
			return false;
		}
		$values = $this->getDirtyAttributes($attributes);
		if (empty($values)) {
			$this->afterSave(false);
			return 0;
		}
		$condition = $this->getOldPrimaryKey(true);
		$lock = $this->optimisticLock();
		if ($lock !== null) {
			if (!isset($values[$lock])) {
				$values[$lock] = $this->$lock + 1;
			}
			$condition[$lock] = $this->$lock;
		}
		// We do not check the return value of update() because it's possible
		// that it doesn't change anything and thus returns 0.
		$rows = static::getCollection()->update($condition, $values);

		if ($lock !== null && !$rows) {
			throw new StaleObjectException('The object being updated is outdated.');
		}

		foreach ($values as $name => $value) {
			$this->setOldAttribute($name, $this->getAttribute($name));
		}
		$this->afterSave(false);
		return $rows;
	}

	public function getContent()
	{
		$file = $this->getAttribute('file');
		if (empty($file)) {
			return null;
		}
		if ($file instanceof \MongoGridFSFile) {
			return $file->getBytes();
		}
	}

	public function getFileName()
	{
		$file = $this->getAttribute('file');
		if (empty($file)) {
			return null;
		}
		if ($file instanceof \MongoGridFSFile) {
			return $file->getFilename();
		}
	}

}