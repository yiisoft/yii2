<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb\file;

use Yii;

/**
 * Query represents Mongo "find" operation for GridFS collection.
 *
 * Query behaves exactly as regular [[\yii\mongodb\Query]].
 * Found files will be represented as arrays of file document attributes with
 * additional 'file' key, which stores [[\MongoGridFSFile]] instance.
 *
 * @property Collection $collection Collection instance. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Query extends \yii\mongodb\Query
{
	/**
	 * Returns the Mongo collection for this query.
	 * @param \yii\mongodb\Connection $db Mongo connection.
	 * @return Collection collection instance.
	 */
	public function getCollection($db = null)
	{
		if ($db === null) {
			$db = Yii::$app->getComponent('mongodb');
		}
		return $db->getFileCollection($this->from);
	}

	/**
	 * @param \MongoGridFSCursor $cursor Mongo cursor instance to fetch data from.
	 * @param boolean $all whether to fetch all rows or only first one.
	 * @param string|callable $indexBy value to index by.
	 * @return array|boolean result.
	 * @see Query::fetchRows()
	 */
	protected function fetchRowsInternal($cursor, $all, $indexBy)
	{
		$result = [];
		if ($all) {
			foreach ($cursor as $file) {
				$row = $file->file;
				$row['file'] = $file;
				if ($indexBy !== null) {
					if (is_string($indexBy)) {
						$key = $row[$indexBy];
					} else {
						$key = call_user_func($indexBy, $row);
					}
					$result[$key] = $row;
				} else {
					$result[] = $row;
				}
			}
		} else {
			if ($cursor->hasNext()) {
				$file = $cursor->getNext();
				$result = $file->file;
				$result['file'] = $file;
			} else {
				$result = false;
			}
		}
		return $result;
	}
}
