<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\ColumnSchema;

/**
 * @group sphinx
 */
class ColumnSchemaTest extends SphinxTestCase
{
	/**
	 * Data provider for [[testTypeCast]]
	 * @return array test data.
	 */
	public function dataProviderTypeCast()
	{
		return [
			[
				'integer',
				'integer',
				5,
				5
			],
			[
				'integer',
				'integer',
				'5',
				5
			],
			[
				'string',
				'string',
				5,
				'5'
			],
		];
	}

	/**
	 * @dataProvider dataProviderTypeCast
	 *
	 * @param $type
	 * @param $phpType
	 * @param $value
	 * @param $expectedResult
	 */
	public function testTypeCast($type, $phpType, $value, $expectedResult)
	{
		$columnSchema = new ColumnSchema();
		$columnSchema->type = $type;
		$columnSchema->phpType = $phpType;
		$this->assertEquals($expectedResult, $columnSchema->typecast($value));
	}
}
