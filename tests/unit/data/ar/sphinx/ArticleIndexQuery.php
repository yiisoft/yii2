<?php
namespace yiiunit\data\ar\sphinx;
use yii\sphinx\ActiveQuery;

/**
 * ArticleIndexQuery
 */
class ArticleIndexQuery extends ActiveQuery
{
	public static function favoriteAuthor($query)
	{
		$query->andWhere('author_id=1');
	}
}
 