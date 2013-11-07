<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\web;
use yii\web\AssetConverter;

/**
 * @group web
 */
class AssetConverterTest extends \yiiunit\TestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}


	public function testConvert()
	{
		$tmpPath = \Yii::$app->runtimePath . '/assetConverterTest';
		if (!is_dir($tmpPath)) {
			mkdir($tmpPath, 0777, true);
		}
		file_put_contents($tmpPath . '/test.php', <<<EOF
<?php

echo "Hello World!\n";
echo "Hello Yii!";
EOF
		);

		$converter = new AssetConverter();
		$converter->commands['php'] = ['txt', 'php {from} > {to}'];
		$this->assertEquals('test.txt', $converter->convert('test.php', $tmpPath));

		$this->assertTrue(file_exists($tmpPath . '/test.txt'), 'Failed asserting that asset output file exists.');
		$this->assertEquals("Hello World!\nHello Yii!", file_get_contents($tmpPath . '/test.txt'));
	}
}