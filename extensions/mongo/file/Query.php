<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo\file;

use Yii;
use yii\helpers\Json;
use yii\mongo\Exception;

/**
 * Class Query
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Query extends \yii\mongo\Query
{
	/**
	 * Returns the Mongo collection for this query.
	 * @param \yii\mongo\Connection $db Mongo connection.
	 * @return Collection collection instance.
	 */
	public function getCollection($db = null)
	{
		if ($db === null) {
			$db = Yii::$app->getComponent('mongo');
		}
		return $db->getFileCollection($this->from);
	}

	/**
	 * Fetches rows from the given Mongo cursor.
	 * @param \MongoCursor $cursor Mongo cursor instance to fetch data from.
	 * @param boolean $all whether to fetch all rows or only first one.
	 * @param string|callable $indexBy the column name or PHP callback,
	 * by which the query results should be indexed by.
	 * @throws Exception on failure.
	 * @return array|boolean result.
	 */
	protected function fetchRows(\MongoCursor $cursor, $all = true, $indexBy = null)
	{
		$token = 'Querying: ' . Json::encode($cursor->info());
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
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
			Yii::endProfile($token, __METHOD__);
			return $result;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
		}
	}
}