<?php

namespace yiiunit\extensions\sphinx;

use yiiunit\data\ar\sphinx\ActiveRecord;
use yiiunit\data\ar\ActiveRecord as ActiveRecordDb;
use yiiunit\data\ar\sphinx\ArticleIndex;
use yiiunit\data\ar\sphinx\ArticleDb;

/**
 * @group sphinx
 */
class ActiveRelationTest extends SphinxTestCase
{
    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
        ActiveRecordDb::$db = $this->getDbConnection();
    }

    // Tests :

    public function testFindLazy()
    {
        /* @var $article ArticleDb */
        $article = ArticleDb::findOne(['id' => 2]);
        $this->assertFalse($article->isRelationPopulated('index'));
        $index = $article->index;
        $this->assertTrue($article->isRelationPopulated('index'));
        $this->assertTrue($index instanceof ArticleIndex);
        $this->assertEquals(1, count($article->relatedRecords));
        $this->assertEquals($article->id, $index->id);
    }

    public function testFindEager()
    {
        $articles = ArticleDb::find()->with('index')->all();
        $this->assertEquals(2, count($articles));
        $this->assertTrue($articles[0]->isRelationPopulated('index'));
        $this->assertTrue($articles[1]->isRelationPopulated('index'));
        $this->assertTrue($articles[0]->index instanceof ArticleIndex);
        $this->assertTrue($articles[1]->index instanceof ArticleIndex);
    }
}
