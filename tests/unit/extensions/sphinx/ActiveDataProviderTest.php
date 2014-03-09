<?php

namespace yiiunit\extensions\sphinx;

use yii\data\ActiveDataProvider;
use yii\sphinx\Query;
use yiiunit\data\ar\sphinx\ActiveRecord;
use yiiunit\data\ar\sphinx\ArticleIndex;

/**
 * @group sphinx
 */
class ActiveDataProviderTest extends SphinxTestCase
{
	protected function setUp()
	{
		parent::setUp();
		ActiveRecord::$db = $this->getConnection();
	}

	// Tests :

	public function testQuery()
	{
		$query = new Query;
		$query->from('yii2_test_article_index');

		$provider = new ActiveDataProvider([
			'query' => $query,
			'db' => $this->getConnection(),
		]);
		$models = $provider->getModels();
		$this->assertEquals(2, count($models));

		$provider = new ActiveDataProvider([
			'query' => $query,
			'db' => $this->getConnection(),
			'pagination' => [
				'pageSize' => 1,
			]
		]);
		$models = $provider->getModels();
		$this->assertEquals(1, count($models));
	}

	public function testActiveQuery()
	{
		$provider = new ActiveDataProvider([
			'query' => ArticleIndex::find()->orderBy('id ASC'),
		]);
		$models = $provider->getModels();
		$this->assertEquals(2, count($models));
		$this->assertTrue($models[0] instanceof ArticleIndex);
		$this->assertTrue($models[1] instanceof ArticleIndex);
		$this->assertEquals([1, 2], $provider->getKeys());

		$provider = new ActiveDataProvider([
			'query' => ArticleIndex::find(),
			'pagination' => [
				'pageSize' => 1,
			]
		]);
		$models = $provider->getModels();
		$this->assertEquals(1, count($models));
	}
}
