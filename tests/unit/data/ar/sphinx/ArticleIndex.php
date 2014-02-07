<?php
namespace yiiunit\data\ar\sphinx;

class ArticleIndex extends ActiveRecord
{
	public $custom_column;

	public static function indexName()
	{
		return 'yii2_test_article_index';
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

	public static function createQuery()
	{
		return new ArticleIndexQuery(['modelClass' => get_called_class()]);
	}
}