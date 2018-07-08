<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\profile;

use Yii;
use yii\helpers\FileHelper;
use yii\profile\FileTarget;
use yii\profile\Profiler;
use yiiunit\TestCase;

class FileTargetTest extends TestCase
{
    /**
     * @var string
     */
    protected $testFilePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
        $this->testFilePath = Yii::getAlias('@runtime/test-profile');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        if (!empty($this->testFilePath)) {
            FileHelper::removeDirectory($this->testFilePath);
        }
    }

    public function testExport()
    {
        $profiler = new Profiler();

        $filename = $this->testFilePath . DIRECTORY_SEPARATOR . 'test.txt';
        $profiler->addTarget(new FileTarget(['filename' => $filename]));

        $profiler->begin('test-export', ['category' => 'test-category']);
        $profiler->end('test-export', ['category' => 'test-category']);
        $profiler->flush();

        $this->assertTrue(file_exists($filename));

        $fileContent = file_get_contents($filename);
        $this->assertContains('[test-category] test-export', $fileContent);
    }
}