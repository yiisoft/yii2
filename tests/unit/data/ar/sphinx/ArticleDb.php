<?php

namespace yiiunit\data\ar\sphinx;

use yii\sphinx\ActiveQuery;
use yiiunit\data\ar\ActiveRecord as ActiveRecordDb;

class ArticleDb extends ActiveRecordDb
{
    public static function tableName()
    {
        return 'yii2_test_article';
    }

    public function getIndex()
    {
        return new ActiveQuery(ArticleIndex::className(), [
            'primaryModel' => $this,
            'link' => ['id' => 'id'],
            'multiple' => false,
        ]);
    }
}
