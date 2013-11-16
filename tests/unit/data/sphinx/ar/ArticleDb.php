<?php

namespace yiiunit\data\sphinx\ar;

use yiiunit\data\ar;

class ArticleDb extends ActiveRecord
{
	public static function tableName()
	{
		return 'yii2_test_article';
	}
}