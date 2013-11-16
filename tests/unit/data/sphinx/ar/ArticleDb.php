<?php

namespace yiiunit\data\sphinx\ar;

use yiiunit\data\ar\ActiveRecord as ActiveRecordDb;

class ArticleDb extends ActiveRecordDb
{
	public static function tableName()
	{
		return 'yii2_test_article';
	}
}