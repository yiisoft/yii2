<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\helpers\FileHelper;
use yii\web\AssetConverter;

/**
 * @group web
 */
class AssetConverterTest extends \yiiunit\TestCase
{
    /**
     * @var string temporary files path
     */
    protected $tmpPath;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->tmpPath = \Yii::$app->runtimePath . '/assetConverterTest_' . getmypid();
        if (!is_dir($this->tmpPath)) {
            mkdir($this->tmpPath, 0777, true);
        }
    }

    protected function tearDown()
    {
        if (is_dir($this->tmpPath)) {
            FileHelper::removeDirectory($this->tmpPath);
        }
        parent::tearDown();
    }

    // Tests :

    public function testConvert()
    {
        $tmpPath = $this->tmpPath;
        file_put_contents($tmpPath . '/test.php', <<<EOF
<?php

echo "Hello World!\n";
echo "Hello Yii!";
EOF
        );

        $converter = new AssetConverter();
        $converter->commands['php'] = ['txt', 'php {from} > {to}'];
        $this->assertEquals('test.txt', $converter->convert('test.php', $tmpPath));

        $this->assertFileExists($tmpPath . '/test.txt', 'Failed asserting that asset output file exists.');
        $this->assertStringEqualsFile($tmpPath . '/test.txt', "Hello World!\nHello Yii!");
    }

    /**
     * @depends testConvert
     */
    public function testForceConvert()
    {
        $tmpPath = $this->tmpPath;
        file_put_contents($tmpPath . '/test.php', <<<'EOF'
<?php

echo microtime();
EOF
        );

        $converter = new AssetConverter();
        $converter->commands['php'] = ['txt', 'php {from} > {to}'];

        $converter->convert('test.php', $tmpPath);
        $initialConvertTime = file_get_contents($tmpPath . '/test.txt');

        usleep(1);
        $converter->convert('test.php', $tmpPath);
        $this->assertStringEqualsFile($tmpPath . '/test.txt', $initialConvertTime);

        $converter->forceConvert = true;
        $converter->convert('test.php', $tmpPath);
        $this->assertNotEquals($initialConvertTime, file_get_contents($tmpPath . '/test.txt'));
    }
}
