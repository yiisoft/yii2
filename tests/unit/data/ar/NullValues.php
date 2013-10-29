<?php

namespace yiiunit\data\ar;

/**
 * Class NullValues
 *
 * @property integer $id
 * @property integer $var1
 * @property integer $var2
 * @property integer $var3
 * @property string $stringcol
 */
class NullValues extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_null_values';
	}
}
