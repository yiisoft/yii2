<?php

namespace yiiunit\extensions\sphinx;

use yiiunit\data\ar\sphinx\ActiveRecord;
use yiiunit\data\ar\ActiveRecord as ActiveRecordDb;
use yiiunit\data\ar\sphinx\ArticleIndex;
use yiiunit\data\ar\sphinx\ArticleDb;

/**
 * @group sphinx
 */
class ExternalActiveRelationTest extends SphinxTestCase
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
        /* @var $article ArticleIndex */
        $article = ArticleIndex::findOne(['id' => 2]);

        // has one :
        $this->assertFalse($article->isRelationPopulated('source'));
        $source = $article->source;
        $this->assertTrue($article->isRelationPopulated('source'));
        $this->assertTrue($source instanceof ArticleDb);
        $this->assertEquals(1, count($article->relatedRecords));

        // has many :
        /*$this->assertFalse($article->isRelationPopulated('tags'));
        $tags = $article->tags;
        $this->assertTrue($article->isRelationPopulated('tags'));
        $this->assertEquals(3, count($tags));
        $this->assertTrue($tags[0] instanceof TagDb);*/
    }

    public function testFindEager()
    {
        // has one :
        $articles = ArticleIndex::find()->with('source')->all();
        $this->assertEquals(2, count($articles));
        $this->assertTrue($articles[0]->isRelationPopulated('source'));
        $this->assertTrue($articles[1]->isRelationPopulated('source'));
        $this->assertTrue($articles[0]->source instanceof ArticleDb);
        $this->assertTrue($articles[1]->source instanceof ArticleDb);

        // has many :
        /*$articles = ArticleIndex::find()->with('tags')->all();
        $this->assertEquals(2, count($articles));
        $this->assertTrue($articles[0]->isRelationPopulated('tags'));
        $this->assertTrue($articles[1]->isRelationPopulated('tags'));*/
    }

    /**
     * @depends testFindEager
     */
    public function testFindWithSnippets()
    {
        $articles = ArticleIndex::find()
            ->match('about')
            ->with('source')
            ->snippetByModel()
            ->all();
        $this->assertEquals(2, count($articles));
    }
}
