<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo\file;

use yii\base\InvalidParamException;
use yii\db\StaleObjectException;
use yii\web\UploadedFile;

/**
 * ActiveRecord is the base class for classes representing Mongo GridFS files in terms of objects.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveRecord extends \yii\mongo\ActiveRecord
{
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
	 * Returns the list of all attribute names of the model.
	 * This method could be overridden by child classes to define available attributes.
	 * Note: primary key attribute "_id" should be always present in returned array.
	 * @return array list of attribute names.
	 */
	public function attributes()
	{
		return [
			'_id',
			'filename',
			'uploadDate',
			'length',
			'chunkSize',
			'md5',
			'file',
			'newFileContent'
		];
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
		if (array_key_exists('newFileContent', $values)) {
			$fileContent = $values['newFileContent'];
			unset($values['newFileContent']);
			unset($values['file']);
			$newId = $collection->insertFileContent($fileContent, $values);
		} elseif (array_key_exists('file', $values)) {
			$file = $values['file'];
			if ($file instanceof UploadedFile) {
				$fileName = $file->tempName;
			} elseif (is_string($file)) {
				if (file_exists($file)) {
					$fileName = $file;
				} else {
					throw new InvalidParamException("File '{$file}' does not exist.");
				}
			} else {
				throw new InvalidParamException('Unsupported type of "file" attribute.');
			}
			unset($values['newFileContent']);
			unset($values['file']);
			$newId = $collection->insertFile($fileName, $values);
		} else {
			$newId = $collection->insert($values);
		}
		$this->setAttribute('_id', $newId);
		foreach ($values as $name => $value) {
			$this->setOldAttribute($name, $value);
		}
		$this->afterSave(true);
		return true;
	}

	/**
	 * @see ActiveRecord::update()
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

		$collection = static::getCollection();
		if (array_key_exists('newFileContent', $values)) {
			$fileContent = $values['newFileContent'];
			unset($values['newFileContent']);
			unset($values['file']);
			$values['_id'] = $this->getAttribute('_id');
			$this->deleteInternal();
			$collection->insertFileContent($fileContent, $values);
			$rows = 1;
			$this->setAttribute('newFileContent', null);
			$this->setAttribute('file', null);
		} elseif (array_key_exists('file', $values)) {
			$file = $values['file'];
			if ($file instanceof UploadedFile) {
				$fileName = $file->tempName;
			} elseif (is_string($file)) {
				if (file_exists($file)) {
					$fileName = $file;
				} else {
					throw new InvalidParamException("File '{$file}' does not exist.");
				}
			} else {
				throw new InvalidParamException('Unsupported type of "file" attribute.');
			}
			unset($values['newFileContent']);
			unset($values['file']);
			$values['_id'] = $this->getAttribute('_id');
			$this->deleteInternal();
			$collection->insertFile($fileName, $values);
			$rows = 1;
			$this->setAttribute('newFileContent', null);
			$this->setAttribute('file', null);
		} else {
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
			$rows = $collection->update($condition, $values);
			if ($lock !== null && !$rows) {
				throw new StaleObjectException('The object being updated is outdated.');
			}
		}

		foreach ($values as $name => $value) {
			$this->setOldAttribute($name, $this->getAttribute($name));
		}
		$this->afterSave(false);
		return $rows;
	}

	/**
	 * Refreshes the [[file]] attribute from file collection, using current primary key.
	 * @return \MongoGridFSFile|null refreshed file value.
	 */
	public function refreshFile()
	{
		$mongoFile = $this->getCollection()->get($this->getPrimaryKey());
		$this->setAttribute('file', $mongoFile);
		return $mongoFile;
	}

	/**
	 * Returns the associated file content.
	 * @return null|string file content.
	 * @throws \yii\base\InvalidParamException on invalid file value.
	 */
	public function getFileContent()
	{
		$file = $this->getAttribute('file');
		if (empty($file) && !$this->getIsNewRecord()) {
			$file = $this->refreshFile();
		}
		if (empty($file)) {
			return null;
		} elseif ($file instanceof \MongoGridFSFile) {
			$fileSize = $file->getSize();
			if (empty($fileSize)) {
				return null;
			} else {
				return $file->getBytes();
			}
		} elseif ($file instanceof UploadedFile) {
			return file_get_contents($file->tempName);
		} elseif (is_string($file)) {
			if (file_exists($file)) {
				return file_get_contents($file);
			}
		} else {
			throw new InvalidParamException('Unsupported type of "file" attribute.');
		}
	}
}