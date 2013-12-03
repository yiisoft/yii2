<?php

namespace yiiunit\data\ar\sphinx;

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
		return $this->hasOne(ArticleDb::className(), ['id' => 'id']);
	}

	public function getTags()
	{
		return $this->hasMany(TagDb::className(), ['id' => 'tag']);
	}

	public function getSnippetSource()
	{
		return $this->source->content;
	}
}