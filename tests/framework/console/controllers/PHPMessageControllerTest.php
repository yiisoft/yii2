<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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

        if (defined('HHVM_VERSION')) {
            // use eval() to bypass HHVM content cache
            // https://github.com/facebook/hhvm/issues/1447
            $content = file_get_contents($messageFilePath);
            return eval(substr($content, strpos($content, 'return ')));
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

    public function messageFileCategoriesDataProvider(){
        return [
            'removeUnused:false - unused category should not be removed - normal category' => ['test_delete_category', true, false, true],
            'removeUnused:false - unused category should not be removed - nested category' => ['nested/category', true, false, true],
            'removeUnused:false - unused category should not be removed - nested 3 level category' => ['multi-level/nested/category', true, false, true],

            'removeUnused:false - used category should not be removed - normal category' => ['test_delete_category', false, false, true],
            'removeUnused:false - used category should not be removed - nested category' => ['nested/category', false, false, true],
            'removeUnused:false - used category should not be removed - nested 3 level category' => ['multi-level/nested/category', false, false, true],

            'removeUnused:true - used category should not be removed - normal category' => ['test_delete_category', false, true, true],
            'removeUnused:true - used category should not be removed - nested category' => ['nested/category', false, true, true],
            'removeUnused:true - used category should not be removed - nested 3 level category' => ['multi-level/nested/category', false, true, true],

            'removeUnused:true - unused category should be removed - normal category' => ['test_delete_category', true, true, false],
            'removeUnused:true - unused category should be removed - nested category' => ['nested/category', true, true, false],
            'removeUnused:true - unused category should be removed - nested 3 level category' => ['multi-level/nested/category', true, true, false],
        ];
    }

    /**
     * @dataProvider messageFileCategoriesDataProvider
     */
    public function testRemoveUnusedBehavior($category, $isUnused, $removeUnused, $isExpectedToExist)
    {
        $this->saveMessages(['test message' => 'test translation'], $category);
        $filePath = $this->getMessageFilePath($category);

        $this->saveConfigFile($this->getConfig([
            'removeUnused' => $removeUnused,
        ]));

        if (!$isUnused) {
            $message = 'test message';
            $sourceFileContent = "Yii::t('{$category}', '{$message}');";
            $this->createSourceFile($sourceFileContent);
        }

        $this->runMessageControllerAction('extract', [$this->configFileName]);
        if ($isExpectedToExist) {
            $this->assertFileExists($filePath);
        } else {
            $this->assertFileNotExists($filePath);
        }
    }
}
