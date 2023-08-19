<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\console\controllers\MessageController;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yiiunit\TestCase;

/**
 * Base for [[\yii\console\controllers\MessageController]] unit tests.
 * @see MessageController
 */
abstract class BaseMessageControllerTest extends TestCase
{
    protected $sourcePath = '';
    protected $configFileName = '';
    protected $language = 'en';

    public function setUp()
    {
        $this->mockApplication();
        $this->sourcePath = Yii::getAlias('@yiiunit/runtime/test_source');
        FileHelper::createDirectory($this->sourcePath, 0777);
        if (!file_exists($this->sourcePath)) {
            $this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
        }
        $this->configFileName = $this->generateConfigFileName();
    }

    /**
     * Generate random config name.
     *
     * @return string
     */
    protected function generateConfigFileName()
    {
        $this->configFileName = Yii::getAlias('@yiiunit/runtime')
            . DIRECTORY_SEPARATOR . 'message_controller_test_config-' . md5(uniqid()) . '.php';

        return $this->configFileName;
    }

    public function tearDown()
    {
        FileHelper::removeDirectory($this->sourcePath);
        if (file_exists($this->configFileName)) {
            unlink($this->configFileName);
        }
    }

    /**
     * Creates test message controller instance.
     * @return MessageControllerMock message command instance.
     */
    protected function createMessageController()
    {
        $module = $this->getMockBuilder('yii\\base\\Module')
            ->setMethods(['fake'])
            ->setConstructorArgs(['console'])
            ->getMock();
        $messageController = new MessageControllerMock('message', $module);
        $messageController->interactive = false;

        return $messageController;
    }

    /**
     * Emulates running of the message controller action.
     * @param  string $actionID id of action to be run.
     * @param  array  $args     action arguments.
     * @return string command output.
     */
    protected function runMessageControllerAction($actionID, array $args = [])
    {
        $controller = $this->createMessageController();
        $controller->run($actionID, $args);
        return $controller->flushStdOutBuffer();
    }

    /**
     * Creates message command config file named as [[configFileName]].
     * @param array $config message command config.
     */
    protected function saveConfigFile(array $config)
    {
        if (file_exists($this->configFileName)) {
            unlink($this->configFileName);
        }
        $fileContent = '<?php return ' . VarDumper::export($config) . ';';
        // save new config on random name to bypass HHVM cache
        // https://github.com/facebook/hhvm/issues/1447
        file_put_contents($this->generateConfigFileName(), $fileContent);
    }

    /**
     * Creates source file with given content.
     * @param string $content file content
     * @return string path to source file
     */
    protected function createSourceFile($content)
    {
        $fileName = $this->sourcePath . DIRECTORY_SEPARATOR . md5(uniqid()) . '.php';
        file_put_contents($fileName, "<?php\n" . $content);
        return $fileName;
    }

    /**
     * Saves messages.
     *
     * @param array $messages
     * @param string $category
     */
    abstract protected function saveMessages($messages, $category);

    /**
     * Loads messages.
     *
     * @param string $category
     * @return array
     */
    abstract protected function loadMessages($category);

    /**
     * @return array default config
     */
    abstract protected function getDefaultConfig();

    /**
     * Returns config.
     *
     * @param array $additionalConfig
     * @return array
     */
    protected function getConfig($additionalConfig = [])
    {
        return array_merge($this->getDefaultConfig(), $additionalConfig);
    }

    // Tests:

    public function testActionConfig()
    {
        $configFileName = $this->configFileName;
        $out = $this->runMessageControllerAction('config', [$configFileName]);
        $this->assertFileExists($configFileName,
            "Unable to create config file from template. Command output:\n\n" . $out);
    }

    public function testActionConfigSubDir()
    {
        $configFileName = Yii::getAlias('@yiiunit/runtime/not_existing_subdir') . DIRECTORY_SEPARATOR . 'message_controller_test_config-' . md5(uniqid()) . '.php';
        $out = $this->runMessageControllerAction('config', [$configFileName]);
        $this->assertFileExists($configFileName, "Unable to create config file in subdirectory. Command output:\n\n" . $out);
    }

    public function testConfigFileNotExist()
    {
        $this->expectException('yii\\console\\Exception');
        $this->runMessageControllerAction('extract', ['not_existing_file.php']);
    }

    public function testCreateTranslation()
    {
        $category = 'test.category1';
        $message = 'test message';
        $message2 = 'test message 2';
        $sourceFileContent = "Yii::t('{$category}', '{$message}');\n\Yii::t('{$category}', '{$message2}');";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig());
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages($category);
        $this->assertArrayHasKey($message, $messages, "\"$message\" is missing in translation file. Command output:\n\n" . $out);
        $this->assertArrayHasKey($message2, $messages, "\"$message2\" is missing in translation file. Command output:\n\n" . $out);
    }

    /**
     * @depends testCreateTranslation
     */
    public function testNothingToSave()
    {
        $category = 'test_category2';
        $message = 'test message';
        $sourceFileContent = "Yii::t('{$category}', '{$message}');";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig());
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);
        $out .= $this->runMessageControllerAction('extract', [$this->configFileName]);

        $this->assertNotFalse(strpos($out, 'Nothing to save'),
            "Controller should respond with \"Nothing to save\" if there's nothing to update. Command output:\n\n" . $out);
    }

    /**
     * @depends testCreateTranslation
     */
    public function testMerge()
    {
        $category = 'test_category3';

        $existingMessage = 'test existing message';
        $existingMessageTranslation = 'test existing message translation';
        $this->saveMessages(
            [$existingMessage => $existingMessageTranslation],
            $category
        );

        $newMessage = 'test new message';
        $sourceFileContent = "Yii::t('{$category}', '{$existingMessage}');";
        $sourceFileContent .= "Yii::t('{$category}', '{$newMessage}');";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig());
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages($category);
        $this->assertArrayHasKey($newMessage, $messages, "Unable to add new message: \"$newMessage\". Command output:\n\n" . $out);
        $this->assertArrayHasKey($existingMessage, $messages, "Unable to keep existing message: \"$existingMessage\". Command output:\n\n" . $out);
        $this->assertEquals('', $messages[$newMessage], "Wrong new message content. Command output:\n\n" . $out);
        $this->assertEquals($existingMessageTranslation, $messages[$existingMessage], "Unable to keep existing message content. Command output:\n\n" . $out);
    }

    /**
     * @depends testMerge
     */
    public function testMarkObsoleteMessages()
    {
        $category = 'category';

        $obsoleteMessage = 'obsolete message';
        $obsoleteTranslation = 'obsolete translation';
        $this->saveMessages([$obsoleteMessage => $obsoleteTranslation], $category);

        $sourceFileContent = "Yii::t('{$category}', 'any new message');";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig(['removeUnused' => false]));
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages($category);

        $this->assertArrayHasKey($obsoleteMessage, $messages, "Obsolete message should not be removed. Command output:\n\n" . $out);
        $this->assertEquals('@@' . $obsoleteTranslation . '@@', $messages[$obsoleteMessage], "Obsolete message was not marked properly. Command output:\n\n" . $out);
    }

    /**
     * @depends testMerge
     */
    public function removeObsoleteMessages()
    {
        $category = 'category';

        $obsoleteMessage = 'obsolete message';
        $obsoleteTranslation = 'obsolete translation';
        $this->saveMessages([$obsoleteMessage => $obsoleteTranslation], $category);

        $sourceFileContent = "Yii::t('{$category}', 'any new message');";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig(['removeUnused' => true]));
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages($category);

        $this->assertArrayHasKey($obsoleteMessage, $messages, "Obsolete message should be removed. Command output:\n\n" . $out);
    }

    /**
     * @depends testMerge
     */
    public function testMergeWithContentZero()
    {
        $category = 'test_category5';

        $zeroMessage = 'test zero message';
        $zeroMessageContent = '0';
        $falseMessage = 'test false message';
        $falseMessageContent = 'false';
        $this->saveMessages([
            $zeroMessage => $zeroMessageContent,
            $falseMessage => $falseMessageContent,
        ], $category);

        $newMessage = 'test new message';
        $sourceFileContent = "Yii::t('{$category}', '{$zeroMessage}');";
        $sourceFileContent .= "Yii::t('{$category}', '{$falseMessage}');";
        $sourceFileContent .= "Yii::t('{$category}', '{$newMessage}');";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig());
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages($category);
        $this->assertSame($zeroMessageContent,
            $messages[$zeroMessage],
            "Message content \"0\" is lost. Command output:\n\n" . $out);
        $this->assertSame($falseMessageContent,
            $messages[$falseMessage],
            "Message content \"false\" is lost. Command output:\n\n" . $out);
    }

    /**
     * @depends testCreateTranslation
     */
    public function testMultipleTranslators()
    {
        $category = 'test_category6';

        $translators = [
            'Yii::t',
            'Custom::translate',
        ];

        $sourceMessages = [
            'first message',
            'second message',
        ];
        $sourceFileContent = '';
        foreach ($sourceMessages as $key => $message) {
            $sourceFileContent .= $translators[$key] . "('{$category}', '{$message}');\n";
        }
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig(['translator' => $translators]));
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages($category);

        foreach ($sourceMessages as $sourceMessage) {
            $this->assertArrayHasKey($sourceMessage, $messages);
        }
    }

    /**
     * @depends testCreateTranslation
     */
    public function testMultipleCategories()
    {
        $category1 = 'category1';
        $category2 = 'category2';

        $message1 = 'message1';
        $message2 = 'message2';
        $message3 = 'message3';

        $this->saveConfigFile($this->getConfig(['removeUnused' => true]));

        // Generate initial translation
        $sourceFileContent = "Yii::t('{$category1}', '{$message1}'); Yii::t('{$category2}', '{$message2}');";
        $source = $this->createSourceFile($sourceFileContent);
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);
        unlink($source);

        $messages1 = $this->loadMessages($category1);
        $messages2 = $this->loadMessages($category2);

        $this->assertArrayHasKey($message1, $messages1, "message1 not found in category1. Command output:\n\n" . $out);
        $this->assertArrayHasKey($message2, $messages2, "message2 not found in category2. Command output:\n\n" . $out);
        $this->assertArrayNotHasKey($message3, $messages2, "message3 found in category2. Command output:\n\n" . $out);

        // Change source code, run translation again
        $sourceFileContent = "Yii::t('{$category1}', '{$message1}'); Yii::t('{$category2}', '{$message3}');";
        $source = $this->createSourceFile($sourceFileContent);
        $out .= "\n" . $this->runMessageControllerAction('extract', [$this->configFileName]);
        unlink($source);

        $messages1 = $this->loadMessages($category1);
        $messages2 = $this->loadMessages($category2);
        $this->assertArrayHasKey($message1, $messages1, "message1 not found in category1. Command output:\n\n" . $out);
        $this->assertArrayHasKey($message3, $messages2, "message3 not found in category2. Command output:\n\n" . $out);
        $this->assertArrayNotHasKey($message2, $messages2, "message2 found in category2. Command output:\n\n" . $out);
    }

    public function testIgnoreCategories()
    {
        $category1 = 'category1';
        $category2 = 'category2';
        $category3_wildcard = 'category3*';
        $category3_test = 'category3-test';

        $message1 = 'message1';
        $message2 = 'message2';
        $message3 = 'message3';

        $this->saveConfigFile($this->getConfig(['ignoreCategories' => [$category2, $category3_wildcard]]));

        // Generate initial translation
        $sourceFileContent = "Yii::t('{$category1}', '{$message1}'); Yii::t('{$category2}', '{$message2}'); Yii::t('{$category3_test}', '{$message3}');";
        $source = $this->createSourceFile($sourceFileContent);
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);
        unlink($source);

        $messages1 = $this->loadMessages($category1);
        $messages2 = $this->loadMessages($category2);
        $messages3 = $this->loadMessages($category3_test);

        $this->assertArrayHasKey($message1, $messages1, "{$message1} not found in {$category1}. Command output:\n\n" . $out);
        $this->assertArrayNotHasKey($message2, $messages2, "{$message2} found in {$category2}. Command output:\n\n" . $out);
        $this->assertArrayNotHasKey($message3, $messages2, "{$message3} found in {$category2}. Command output:\n\n" . $out);
        $this->assertArrayNotHasKey($message3, $messages3, "{$message3} found in {$category3_test}. Command output:\n\n" . $out);

        // Change source code, run translation again
        $sourceFileContent = "Yii::t('{$category1}', '{$message1}'); Yii::t('{$category2}', '{$message3}');";
        $source = $this->createSourceFile($sourceFileContent);
        $out .= "\n" . $this->runMessageControllerAction('extract', [$this->configFileName]);
        unlink($source);

        $messages1 = $this->loadMessages($category1);
        $messages2 = $this->loadMessages($category2);
        $this->assertArrayHasKey($message1, $messages1, "{$message1} not found in {$category1}. Command output:\n\n" . $out);
        $this->assertArrayNotHasKey($message2, $messages2, "{$message2} found in {$category2}. Command output:\n\n" . $out);
        $this->assertArrayNotHasKey($message3, $messages2, "{$message3} not found in {$category2}. Command output:\n\n" . $out);
    }

    /**
     * @depends testCreateTranslation
     *
     * @see https://github.com/yiisoft/yii2/issues/8286
     */
    public function testCreateTranslationFromNested()
    {
        $category = 'test.category1';
        $mainMessage = 'main message';
        $nestedMessage = 'nested message';
        $sourceFileContent = "Yii::t('{$category}', '{$mainMessage}', ['param' => Yii::t('{$category}', '{$nestedMessage}')]);";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig());
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages($category);
        $this->assertArrayHasKey($mainMessage, $messages, "\"$mainMessage\" is missing in translation file. Command output:\n\n" . $out);
        $this->assertArrayHasKey($nestedMessage, $messages, "\"$nestedMessage\" is missing in translation file. Command output:\n\n" . $out);
    }

    /**
     * @depends testCreateTranslation
     *
     * @see https://github.com/yiisoft/yii2/issues/11502
     */
    public function testMissingLanguage()
    {
        $category = 'multiLangCategory';
        $mainMessage = 'multiLangMessage';
        $sourceFileContent = "Yii::t('{$category}', '{$mainMessage}');";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig());
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);

        $secondLanguage = 'pl';
        $this->saveConfigFile($this->getConfig(['languages' => [$this->language, $secondLanguage]]));
        $out .= $this->runMessageControllerAction('extract', [$this->configFileName]);

        $firstLanguage = $this->language;
        $this->language = $secondLanguage;
        $messages = $this->loadMessages($category);
        $this->language = $firstLanguage;
        $this->assertArrayHasKey($mainMessage, $messages, "\"$mainMessage\" for language \"$secondLanguage\" is missing in translation file. Command output:\n\n" . $out);
    }

    /**
     * @depends testCreateTranslation
     *
     * @see https://github.com/yiisoft/yii2/issues/13824
     */
    public function testCreateTranslationFromConcatenatedString()
    {
        $category = 'test.category1';
        $mainMessage = 'main message second message third message';
        $sourceFileContent = "Yii::t('{$category}', 'main message' .   \" second message\".' third message');";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig());
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages($category);
        $this->assertArrayHasKey($mainMessage, $messages,
            "\"$mainMessage\" is missing in translation file. Command output:\n\n" . $out);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14016
     */
    public function testShouldNotMarkUnused()
    {
        $category = 'testShouldNotMarkUnused';

        $key1 = 'key1';
        $key2 = 'key2';

        $this->saveMessages(
            [
                $key1 => '',
                $key2 => '',
            ],
            $category
        );

        $sourceFileContent = 'Yii::t("testShouldNotMarkUnused", "test");';
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig(['markUnused' => false]));
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);
        $messages = $this->loadMessages($category);

        $this->assertArrayHasKey($key1, $messages, "$key1 isn't there. Command output:\n\n" . $out);
        $this->assertArrayHasKey($key2, $messages, "$key2 isn't there. Command output:\n\n" . $out);

        $value1 = $messages[$key1];
        $value2 = $messages[$key2];

        $this->assertEquals('', $value1, "Message at $key1 should be empty but it is $value1. Command output:\n\n" . $out);
        $this->assertEquals('', $value2, "Message at $key2 should be empty but it is $value2. Command output:\n\n" . $out);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/13792
     */
    public function testShouldNotRemoveUnused()
    {
        $category = 'my';

        $key1 = 'test';
        $key2 = 'unused';

        $this->saveMessages(
            [
                $key1 => 'test translation',
                $key2 => 'unused translation',
            ],
            $category
        );

        $sourceFileContent = 'Yii::t("my", "test");';
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig([
            'removeUnused' => false,
            'markUnused' => false,
        ]));
        $out = $this->runMessageControllerAction('extract', [$this->configFileName]);
        $messages = $this->loadMessages($category);

        $this->assertArrayHasKey($key1, $messages, "$key1 isn't there. Command output:\n\n" . $out);
        $this->assertArrayHasKey($key2, $messages, "$key2 isn't there. Command output:\n\n" . $out);

        $value1 = $messages[$key1];
        $value2 = $messages[$key2];

        $this->assertEquals('test translation', $value1, "Message at $key1 should be be \"test translation\" but it is $value1. Command output:\n\n" . $out);
        $this->assertEquals('unused translation', $value2, "Message at $key2 should be \"unused translation\" but it is $value2. Command output:\n\n" . $out);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/16828
     */
    public function testPartialTranslator()
    {
        $category = 'category';
        $negativeKey1 = 'Should not find this';
        $negativeKey2 = 'not applicable';
        $negativeKey3 = 'do not extract this';
        $positiveKey1 = 'but find this';
        $positiveKey2 = 'this substring should be extracted';

        $sourceFileContent = "
            <?= Yii::t('{$category}', '{$negativeKey1}') ?>
            <?= t('{$category}', '{$positiveKey1}') ?>
            <?= \$this->t('{$category}', '{$negativeKey2}', [
                'subString1' => Yii::t('{$category}', '{$negativeKey3}'),
                'subString2' => t('{$category}', '{$positiveKey2}'),
            ]) ?>
        ";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig(['translator' => ['t']]));
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages($category);

        $this->assertArrayNotHasKey($negativeKey1, $messages);
        $this->assertArrayNotHasKey($negativeKey2, $messages);
        $this->assertArrayNotHasKey($negativeKey3, $messages);
        $this->assertArrayHasKey($positiveKey1, $messages);
        $this->assertArrayHasKey($positiveKey2, $messages);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17098
     */
    public function testMessageExtractActionWhenMessageUsingParamsReturnedFromMethodCalls()
    {
        $sourceFileContent = "
            echo PHP_EOL, Yii::t('app', '1. Simple message');
            echo PHP_EOL, Yii::t('app', '2. Message with simple param {val}', [
                'val' => 'today',
            ]);
            echo PHP_EOL, Yii::t('app', '3. Message with param from function call {val}', [
                'val' => date('Y-m-d'),
            ]);

            // the next call creates the bug:
            echo PHP_EOL, Yii::t('app', '4. Message with param from method call {val}', [
                'val' => \Yii::\$app->formatter->asDecimal(23, 4),
            ]);

            // after the bug:
            echo PHP_EOL, Yii::t('app', '5. Simple message');
            echo PHP_EOL, Yii::t('app', '6. Message with simple param {val}', [
                'val' => 'today',
            ]);
            echo PHP_EOL, Yii::t('app', '7. Message with param from function call {val}', [
                'val' => date('Y-m-d'),
            ]);
        ";
        $this->createSourceFile($sourceFileContent);

        $this->saveConfigFile($this->getConfig(['translator' => ['Yii::t']]));
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = $this->loadMessages('app');

        $this->assertEquals([
            '1. Simple message' => '',
            '2. Message with simple param {val}' => '',
            '3. Message with param from function call {val}' => '',
            '4. Message with param from method call {val}' => '',
            '5. Simple message' => '',
            '6. Message with simple param {val}' => '',
            '7. Message with param from function call {val}' => '',
        ], $messages);
    }

    public function testMessagesSorting()
    {
        $category = 'test_order_category';
        $key1 = 'key1';
        $key2 = 'key2';

        $sourceFileContent = "Yii::t('{$category}', '{$key1}');Yii::t('{$category}', '{$key2}');";
        $this->createSourceFile($sourceFileContent);

        $this->saveMessages([$key2 => 'already translated'], $category);

        $this->saveConfigFile($this->getConfig([
            'sort' => true,
        ]));
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $keys = array_keys($this->loadMessages($category));
        $this->assertEquals([$key1, $key2], $keys, "The order of messages should be '{$key1}, {$key2}' when sort equals true");


        $this->saveMessages([$key2 => 'already translated'], $category);
        $this->saveConfigFile($this->getConfig([
            'sort' => false,
        ]));
        $this->runMessageControllerAction('extract', [$this->configFileName]);
        $keys = array_keys($this->loadMessages($category));
        $this->assertEquals([$key2, $key1], $keys, "The order of messages should be '{$key2}, {$key1}' when sort equals false and {$key1} was added later");
    }
}

class MessageControllerMock extends MessageController
{
    use StdOutBufferControllerTrait;
}
