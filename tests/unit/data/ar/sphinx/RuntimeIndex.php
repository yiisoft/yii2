<?php

namespace yiiunit\data\ar\sphinx;

class RuntimeIndex extends ActiveRecord
{
	public static function indexName()
	{
		return 'yii2_test_rt_index';
	}
}