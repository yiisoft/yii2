<?php
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
     * @inheritdoc
     */
    protected function getDefaultConfig()
    {
        return [
            'format' => 'php',
            'languages' => [$this->language],
            'sourcePath' => $this->sourcePath,
            'messagePath' => $this->messagePath,
            'overwrite' => true,
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
     * @inheritdoc
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
     * @inheritdoc
     */
    protected function loadMessages($category)
    {
        if (defined('HHVM_VERSION')) {
            // https://github.com/facebook/hhvm/issues/1447
            $this->markTestSkipped('Can not test on HHVM because require is cached.');
        }

        $messageFilePath = $this->getMessageFilePath($category);
        $this->assertTrue(file_exists($messageFilePath), "There's no message file $messageFilePath!");
        return require $messageFilePath;
    }
}