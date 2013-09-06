<?php

namespace yiiunit\framework\db;

use yii\caching\FileCache;
use yii\db\Schema;

class SchemaTest extends DatabaseTestCase
{

	public function testGetPDOType()
	{
		$values = array(
			array(null, \PDO::PARAM_NULL),
			array('', \PDO::PARAM_STR),
			array('hello', \PDO::PARAM_STR),
			array(0, \PDO::PARAM_INT),
			array(1, \PDO::PARAM_INT),
			array(1337, \PDO::PARAM_INT),
			array(true, \PDO::PARAM_BOOL),
			array(false, \PDO::PARAM_BOOL),
			array($fp=fopen(__FILE__, 'rb'), \PDO::PARAM_LOB),
		);

		$schema = $this->getConnection()->schema;

		foreach($values as $value) {
			$this->assertEquals($value[1], $schema->getPdoType($value[0]));
		}
		fclose($fp);
	}

	public function testFindTableNames()
	{
		/** @var Schema $schema */
		$schema = $this->getConnection()->schema;

		$tables = $schema->getTableNames();
		$this->assertTrue(in_array('tbl_customer', $tables));
		$this->assertTrue(in_array('tbl_category', $tables));
		$this->assertTrue(in_array('tbl_item', $tables));
		$this->assertTrue(in_array('tbl_order', $tables));
		$this->assertTrue(in_array('tbl_order_item', $tables));
		$this->assertTrue(in_array('tbl_type', $tables));
	}

	public function testGetTableSchemas()
	{
		/** @var Schema $schema */
		$schema = $this->getConnection()->schema;

		$tables = $schema->getTableSchemas();
		$this->assertEquals(count($schema->getTableNames()), count($tables));
		foreach($tables as $table) {
			$this->assertInstanceOf('yii\db\TableSchema', $table);
		}
	}

	public function testGetNonExistingTableSchema()
	{
		$this->assertNull($this->getConnection()->schema->getTableSchema('nonexisting_table'));
	}

	public function testSchemaCache()
	{
		/** @var Schema $schema */
		$schema = $this->getConnection()->schema;

		$schema->db->enableSchemaCache = true;
		$schema->db->schemaCache = new FileCache();
		$noCacheTable = $schema->getTableSchema('tbl_type', true);
		$cachedTable = $schema->getTableSchema('tbl_type', true);
		$this->assertEquals($noCacheTable, $cachedTable);
	}

	public function testCompositeFk()
	{
		/** @var Schema $schema */
		$schema = $this->getConnection()->schema;

		$table = $schema->getTableSchema('tbl_composite_fk');

		$this->assertCount(1, $table->foreignKeys);
		$this->assertTrue(isset($table->foreignKeys[0]));
		$this->assertEquals('tbl_order_item', $table->foreignKeys[0][0]);
		$this->assertEquals('order_id', $table->foreignKeys[0]['order_id']);
		$this->assertEquals('item_id', $table->foreignKeys[0]['item_id']);
	}
}
