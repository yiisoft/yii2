<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

/**
 * Tests that [[\yii\console\controllers\MessageController]] works as expected with PHP message format.
 */
class PHPMessageControllerTest extends BaseMessageControllerTest
{
    protected $messagePath;

    public function setUp()
    {
        parent::setUp();
        $this->messagePath = Yii::getAlias('@yiiunit/runtime/test_messages');
        FileHelper::createDirectory($this->messagePath, 0777);
    }

    public function tearDown()
    {
        parent::tearDown();
        FileHelper::removeDirectory($this->messagePath);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'format' => 'php',
            'languages' => [$this->language],
            'sourcePath' => $this->sourcePath,
            'messagePath' => $this->messagePath,
            'overwrite' => true,
            'phpFileHeader' => "/*file header*/\n",
            'phpDocBlock' => '/*doc block*/',
        ];
    }

    /**
     * @param string $category
     * @return string message file path
     */
    protected function getMessageFilePath($category)
    {
        return $this->messagePath . '/' . $this->language . '/' . $category . '.php';
    }

    /**
     * {@inheritdoc}
     */
    protected function saveMessages($messages, $category)
    {
        $fileName = $this->getMessageFilePath($category);
        if (file_exists($fileName)) {
            unlink($fileName);
        } else {
            $dirName = dirname($fileName);
            if (!file_exists($dirName)) {
                mkdir($dirName, 0777, true);
            }
        }
        $fileContent = '<?php return ' . VarDumper::export($messages) . ';';
        file_put_contents($fileName, $fileContent);
    }

    /**
     * {@inheritdoc}
     */
    protected function loadMessages($category)
    {
        $messageFilePath = $this->getMessageFilePath($category);

        if (!file_exists($messageFilePath)) {
            return [];
        }

        return require $messageFilePath;
    }

    // By default phpunit runs inherited test after inline tests, so `testCreateTranslation()` would be run after
    // `testCustomFileHeaderAndDocBlock()` (that would break `@depends` annotation). This ensures that
    // `testCreateTranslation() will be run before `testCustomFileHeaderAndDocBlock()`.
    public function testCreateTranslation()
    {
        parent::testCreateTranslation();
    }

    /**
     * @depends testCreateTranslation
     */
    public function testCustomFileHeaderAndDocBlock()
    {
        $category = 'test_headers_category';
        $message = 'test message';
        $sourceFileContent = "Yii::t('{$category}', '{$message}');";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig());
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messageFilePath = $this->getMessageFilePath('test_headers_category');
        $content = file_get_contents($messageFilePath);
        $head = substr($content, 0, strpos($content, 'return '));
        $expected = "<?php\n/*file header*/\n/*doc block*/\n";
        $this->assertEqualsWithoutLE($expected, $head);
    }
}
