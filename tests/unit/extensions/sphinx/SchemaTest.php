<?php

namespace yiiunit\extensions\sphinx;

use yii\caching\FileCache;

/**
 * @group sphinx
 */
class SchemaTest extends SphinxTestCase
{
	public function testFindIndexNames()
	{
		$schema = $this->getConnection()->schema;

		$indexes = $schema->getIndexNames();
		$this->assertContains('yii2_test_article_index', $indexes);
		$this->assertContains('yii2_test_item_index', $indexes);
		$this->assertContains('yii2_test_rt_index', $indexes);
	}

	public function testGetIndexSchemas()
	{
		$schema = $this->getConnection()->schema;

		$indexes = $schema->getIndexSchemas();
		$this->assertEquals(count($schema->getIndexNames()), count($indexes));
		foreach ($indexes as $index) {
			$this->assertInstanceOf('yii\sphinx\IndexSchema', $index);
		}
	}

	public function testGetNonExistingIndexSchema()
	{
		$this->assertNull($this->getConnection()->schema->getIndexSchema('non_existing_index'));
	}

	public function testSchemaRefresh()
	{
		$schema = $this->getConnection()->schema;

		$schema->db->enableSchemaCache = true;
		$schema->db->schemaCache = new FileCache();
		$noCacheIndex = $schema->getIndexSchema('yii2_test_rt_index', true);
		$cachedIndex = $schema->getIndexSchema('yii2_test_rt_index', true);
		$this->assertEquals($noCacheIndex, $cachedIndex);
	}

	public function testGetPDOType()
	{
		$values = [
			[null, \PDO::PARAM_NULL],
			['', \PDO::PARAM_STR],
			['hello', \PDO::PARAM_STR],
			[0, \PDO::PARAM_INT],
			[1, \PDO::PARAM_INT],
			[1337, \PDO::PARAM_INT],
			[true, \PDO::PARAM_BOOL],
			[false, \PDO::PARAM_BOOL],
			[$fp=fopen(__FILE__, 'rb'), \PDO::PARAM_LOB],
		];

		$schema = $this->getConnection()->schema;

		foreach ($values as $value) {
			$this->assertEquals($value[1], $schema->getPdoType($value[0]));
		}
		fclose($fp);
	}

	public function testIndexType()
	{
		$schema = $this->getConnection()->schema;

		$index = $schema->getIndexSchema('yii2_test_article_index');
		$this->assertEquals('local', $index->type);
		$this->assertFalse($index->isRuntime);

		$index = $schema->getIndexSchema('yii2_test_rt_index');
		$this->assertEquals('rt', $index->type);
		$this->assertTrue($index->isRuntime);
	}
}
