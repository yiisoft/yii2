<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class Collection.
 *
 * @property integer $id
 * @property integer $version
 */
class Collection extends ActiveRecord
{
    public static function tableName()
    {
        return 'collection';
    }
	
	public function getItems()
	{
		return $this->hasMany(Item::className(), [
			'id' => 'item_id',
		])->viaTable('collection_item', [
			'collection_id' => 'id',
			'collection_version' => 'version',
		]);
	}

}
