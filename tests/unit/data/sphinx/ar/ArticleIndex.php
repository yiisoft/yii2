<?php

namespace yiiunit\data\sphinx\ar;

use yii\db\ActiveRelation;

class ArticleIndex extends ActiveRecord
{
	public $custom_column;

	public static function indexName()
	{
		return 'yii2_test_article_index';
	}

	public static function favoriteAuthor($query)
	{
		$query->andWhere('author_id=1');
	}

	public function getSource()
	{
		$config = [
			'modelClass' => ArticleDb::className(),
			'primaryModel' => $this,
			'link' => ['id' => 'id'],
			'multiple' => false,
		];
		return new ActiveRelation($config);
	}
}