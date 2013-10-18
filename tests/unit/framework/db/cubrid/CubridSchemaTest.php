<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\framework\db\SchemaTest;

/**
 * @group db
 * @group cubrid
 */
class CubridSchemaTest extends SchemaTest
{
	public $driverName = 'cubrid';

	public function testGetPDOType()
	{
		$values = [
			[null, \PDO::PARAM_NULL],
			['', \PDO::PARAM_STR],
			['hello', \PDO::PARAM_STR],
			[0, \PDO::PARAM_INT],
			[1, \PDO::PARAM_INT],
			[1337, \PDO::PARAM_INT],
			[true, \PDO::PARAM_INT],
			[false, \PDO::PARAM_INT],
			[$fp=fopen(__FILE__, 'rb'), \PDO::PARAM_LOB],
		];

		/** @var Schema $schema */
		$schema = $this->getConnection()->schema;

		foreach($values as $value) {
			$this->assertEquals($value[1], $schema->getPdoType($value[0]));
		}
		fclose($fp);
	}
}
