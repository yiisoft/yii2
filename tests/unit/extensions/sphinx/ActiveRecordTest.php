<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\ActiveQuery;
use yiiunit\data\ar\sphinx\ActiveRecord;
use yiiunit\data\ar\sphinx\ArticleIndex;
use yiiunit\data\ar\sphinx\RuntimeIndex;

/**
 * @group sphinx
 */
class ActiveRecordTest extends SphinxTestCase
{
	protected function setUp()
	{
		parent::setUp();
		ActiveRecord::$db = $this->getConnection();
	}

	protected function tearDown()
	{
		$this->truncateRuntimeIndex('yii2_test_rt_index');
		parent::tearDown();
	}

	// Tests :

	public function testFind()
	{
		// find one
		$result = ArticleIndex::find();
		$this->assertTrue($result instanceof ActiveQuery);
		$article = $result->one();
		$this->assertTrue($article instanceof ArticleIndex);

		// find all
		$articles = ArticleIndex::find()->all();
		$this->assertEquals(2, count($articles));
		$this->assertTrue($articles[0] instanceof ArticleIndex);
		$this->assertTrue($articles[1] instanceof ArticleIndex);

		// find fulltext
		$article = ArticleIndex::find(2);
		$this->assertTrue($article instanceof ArticleIndex);
		$this->assertEquals(2, $article->id);

		// find by column values
		$article = ArticleIndex::find(['id' => 2, 'author_id' => 2]);
		$this->assertTrue($article instanceof ArticleIndex);
		$this->assertEquals(2, $article->id);
		$this->assertEquals(2, $article->author_id);
		$article = ArticleIndex::find(['id' => 2, 'author_id' => 1]);
		$this->assertNull($article);

		// find by attributes
		$article = ArticleIndex::find()->where(['author_id' => 2])->one();
		$this->assertTrue($article instanceof ArticleIndex);
		$this->assertEquals(2, $article->id);

		// find custom column
		$article = ArticleIndex::find()->select(['*', '(5*2) AS custom_column'])
			->where(['author_id' => 1])->one();
		$this->assertEquals(1, $article->id);
		$this->assertEquals(10, $article->custom_column);

		// find count, sum, average, min, max, scalar
		$this->assertEquals(2, ArticleIndex::find()->count());
		$this->assertEquals(1, ArticleIndex::find()->where('id=1')->count());
		$this->assertEquals(3, ArticleIndex::find()->sum('id'));
		$this->assertEquals(1.5, ArticleIndex::find()->average('id'));
		$this->assertEquals(1, ArticleIndex::find()->min('id'));
		$this->assertEquals(2, ArticleIndex::find()->max('id'));
		$this->assertEquals(2, ArticleIndex::find()->select('COUNT(*)')->scalar());

		// scope
		$this->assertEquals(1, ArticleIndex::find()->favoriteAuthor()->count());

		// asArray
		$article = ArticleIndex::find()->where('id=2')->asArray()->one();
		unset($article['add_date']);
		$this->assertEquals([
			'id' => '2',
			'author_id' => '2',
			'tag' => '3,4',
		], $article);

		// indexBy
		$articles = ArticleIndex::find()->indexBy('author_id')->orderBy('id DESC')->all();
		$this->assertEquals(2, count($articles));
		$this->assertTrue($articles['1'] instanceof ArticleIndex);
		$this->assertTrue($articles['2'] instanceof ArticleIndex);

		// indexBy callable
		$articles = ArticleIndex::find()->indexBy(function ($article) {
			return $article->id . '-' . $article->author_id;
		})->orderBy('id DESC')->all();
		$this->assertEquals(2, count($articles));
		$this->assertTrue($articles['1-1'] instanceof ArticleIndex);
		$this->assertTrue($articles['2-2'] instanceof ArticleIndex);
	}

	public function testFindBySql()
	{
		// find one
		$article = ArticleIndex::findBySql('SELECT * FROM yii2_test_article_index ORDER BY id DESC')->one();
		$this->assertTrue($article instanceof ArticleIndex);
		$this->assertEquals(2, $article->author_id);

		// find all
		$articles = ArticleIndex::findBySql('SELECT * FROM yii2_test_article_index')->all();
		$this->assertEquals(2, count($articles));

		// find with parameter binding
		$article = ArticleIndex::findBySql('SELECT * FROM yii2_test_article_index WHERE id=:id', [':id' => 2])->one();
		$this->assertTrue($article instanceof ArticleIndex);
		$this->assertEquals(2, $article->author_id);
	}

	public function testInsert()
	{
		$record = new RuntimeIndex;
		$record->id = 15;
		$record->title = 'test title';
		$record->content = 'test content';
		$record->type_id = 7;
		$record->category = [1, 2];

		$this->assertTrue($record->isNewRecord);

		$record->save();

		$this->assertEquals(15, $record->id);
		$this->assertFalse($record->isNewRecord);
	}

	/**
	 * @depends testInsert
	 */
	public function testUpdate()
	{
		$record = new RuntimeIndex;
		$record->id = 2;
		$record->title = 'test title';
		$record->content = 'test content';
		$record->type_id = 7;
		$record->category = [1, 2];
		$record->save();

		// save
		$record = RuntimeIndex::find(2);
		$this->assertTrue($record instanceof RuntimeIndex);
		$this->assertEquals(7, $record->type_id);
		$this->assertFalse($record->isNewRecord);

		$record->type_id = 9;
		$record->save();
		$this->assertEquals(9, $record->type_id);
		$this->assertFalse($record->isNewRecord);
		$record2 = RuntimeIndex::find(['id' => 2]);
		$this->assertEquals(9, $record2->type_id);

		// replace
		$query = 'replace';
		$rows = RuntimeIndex::find()->match($query)->all();
		$this->assertEmpty($rows);
		$record = RuntimeIndex::find(2);
		$record->content = 'Test content with ' . $query;
		$record->save();
		$rows = RuntimeIndex::find()->match($query);
		$this->assertNotEmpty($rows);

		// updateAll
		$pk = ['id' => 2];
		$ret = RuntimeIndex::updateAll(['type_id' => 55], $pk);
		$this->assertEquals(1, $ret);
		$record = RuntimeIndex::find($pk);
		$this->assertEquals(55, $record->type_id);
	}

	/**
	 * @depends testInsert
	 */
	public function testDelete()
	{
		// delete
		$record = new RuntimeIndex;
		$record->id = 2;
		$record->title = 'test title';
		$record->content = 'test content';
		$record->type_id = 7;
		$record->category = [1, 2];
		$record->save();

		$record = RuntimeIndex::find(2);
		$record->delete();
		$record = RuntimeIndex::find(2);
		$this->assertNull($record);

		// deleteAll
		$record = new RuntimeIndex;
		$record->id = 2;
		$record->title = 'test title';
		$record->content = 'test content';
		$record->type_id = 7;
		$record->category = [1, 2];
		$record->save();

		$ret = RuntimeIndex::deleteAll('id = 2');
		$this->assertEquals(1, $ret);
		$records = RuntimeIndex::find()->all();
		$this->assertEquals(0, count($records));
	}

	public function testCallSnippets()
	{
		$query = 'pencil';
		$source = 'Some data sentence about ' . $query;

		$snippet = ArticleIndex::callSnippets($source, $query);
		$this->assertNotEmpty($snippet, 'Unable to call snippets!');
		$this->assertContains('<b>' . $query . '</b>', $snippet, 'Query not present in the snippet!');

		$rows = ArticleIndex::callSnippets([$source], $query);
		$this->assertNotEmpty($rows, 'Unable to call snippets!');
		$this->assertContains('<b>' . $query . '</b>', $rows[0], 'Query not present in the snippet!');
	}

	public function testCallKeywords()
	{
		$text = 'table pencil';
		$rows = ArticleIndex::callKeywords($text);
		$this->assertNotEmpty($rows, 'Unable to call keywords!');
		$this->assertArrayHasKey('tokenized', $rows[0], 'No tokenized keyword!');
		$this->assertArrayHasKey('normalized', $rows[0], 'No normalized keyword!');
	}
}
