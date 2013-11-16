<?php

namespace yiiunit\extensions\sphinx;

use yiiunit\data\sphinx\ar\ActiveRecord;
use yiiunit\data\ar\ActiveRecord as ActiveRecordDb;
use yiiunit\data\sphinx\ar\ArticleIndex;
use yiiunit\data\sphinx\ar\ArticleDb;

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

	public function testFindLazy()
	{
		/** @var ArticleIndex $article */
		$article = ArticleIndex::find(['id' => 2]);
		$this->assertFalse($article->isRelationPopulated('source'));
		$source = $article->source;
		$this->assertTrue($article->isRelationPopulated('source'));
		$this->assertTrue($source instanceof ArticleDb);
		$this->assertEquals(1, count($article->populatedRelations));
	}

	public function testFindEager()
	{
		$articles = ArticleIndex::find()->with('source')->all();
		$this->assertEquals(2, count($articles));
		$this->assertTrue($articles[0]->isRelationPopulated('source'));
		$this->assertTrue($articles[1]->isRelationPopulated('source'));
		$this->assertTrue($articles[0]->source instanceof ArticleDb);
		$this->assertTrue($articles[1]->source instanceof ArticleDb);
	}
}