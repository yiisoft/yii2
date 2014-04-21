<?php
namespace yiiunit\data\ar\sphinx;

class ArticleIndex extends ActiveRecord
{
    public $custom_column;

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getSnippetSource()
    {
        return $this->source->content;
    }

    /**
     * @return ArticleIndexQuery
     */
    public static function find()
    {
        return new ArticleIndexQuery(get_called_class());
    }
}
