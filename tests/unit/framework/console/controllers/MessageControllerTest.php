<?php

use yiiunit\TestCase;
use yii\console\controllers\MessageController;

/**
 * Unit test for [[\yii\console\controllers\MessageController]].
 * @see MessageController
 *
 * @group console
 */
class MessageControllerTest extends TestCase
{
    protected $sourcePath = '';
    protected $messagePath = '';
    protected $configFileName = '';

    public function setUp()
    {
        $this->mockApplication();
        $this->sourcePath = Yii::getAlias('@yiiunit/runtime/test_source');
        $this->createDir($this->sourcePath);
        if (!file_exists($this->sourcePath)) {
            $this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
        }
        $this->messagePath = Yii::getAlias('@yiiunit/runtime/test_messages');
        $this->createDir($this->messagePath);
        $this->configFileName = Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . 'message_controller_test_config.php';
    }

    public function tearDown()
    {
        $this->removeDir($this->sourcePath);
        $this->removeDir($this->messagePath);
        if (file_exists($this->configFileName)) {
            unlink($this->configFileName);
        }
    }

    /**
     * Creates directory.
     * @param $dirName directory full name
     */
    protected function createDir($dirName)
    {
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
    }

    /**
     * Removes directory.
     * @param $dirName directory full name
     */
    protected function removeDir($dirName)
    {
        if (!empty($dirName) && file_exists($dirName)) {
            $this->removeFileSystemObject($dirName);
        }
    }

    /**
     * Removes file system object: directory or file.
     * @param string $fileSystemObjectFullName file system object full name.
     */
    protected function removeFileSystemObject($fileSystemObjectFullName)
    {
        if (!is_dir($fileSystemObjectFullName)) {
            unlink($fileSystemObjectFullName);
        } else {
            $dirHandle = opendir($fileSystemObjectFullName);
            while (($fileSystemObjectName = readdir($dirHandle)) !== false) {
                if ($fileSystemObjectName === '.' || $fileSystemObjectName === '..') {
                    continue;
                }
                $this->removeFileSystemObject($fileSystemObjectFullName . DIRECTORY_SEPARATOR . $fileSystemObjectName);
            }
            closedir($dirHandle);
            rmdir($fileSystemObjectFullName);
        }
    }

    /**
     * Creates test message controller instance.
     * @return MessageController message command instance.
     */
    protected function createMessageController()
    {
        $module = $this->getMock('yii\\base\\Module', ['fake'], ['console']);
        $messageController = new MessageController('message', $module);
        $messageController->interactive = false;

        return $messageController;
    }

    /**
     * Emulates running of the message controller action.
     * @param  string $actionId id of action to be run.
     * @param  array  $args     action arguments.
     * @return string command output.
     */
    protected function runMessageControllerAction($actionId, array $args = [])
    {
        $controller = $this->createMessageController();
        ob_start();
        ob_implicit_flush(false);
        $controller->run($actionId, $args);

        return ob_get_clean();
    }

    /**
     * Creates message command config file named as [[configFileName]].
     * @param array $config message command config.
     */
    protected function composeConfigFile(array $config)
    {
        if (file_exists($this->configFileName)) {
            unlink($this->configFileName);
        }
        $fileContent = '<?php return ' . var_export($config, true) . ';';
        file_put_contents($this->configFileName, $fileContent);
    }

    /**
     * Creates source file with given content
     * @param string      $content file content
     * @param string|null $name    file self name
     */
    protected function createSourceFile($content, $name = null)
    {
        if (empty($name)) {
            $name = md5(uniqid()) . '.php';
        }
        file_put_contents($this->sourcePath . DIRECTORY_SEPARATOR . $name, $content);
    }

    /**
     * Creates message file with given messages.
     * @param string $name     file name
     * @param array  $messages messages.
     */
    protected function createMessageFile($name, array $messages = [])
    {
        $fileName = $this->messagePath . DIRECTORY_SEPARATOR . $name;
        if (file_exists($fileName)) {
            unlink($fileName);
        } else {
            $dirName = dirname($fileName);
            if (!file_exists($dirName)) {
                mkdir($dirName, 0777, true);
            }
        }
        $fileContent = '<?php return ' . var_export($messages, true) . ';';
        file_put_contents($fileName, $fileContent);
    }

    // Tests:

    public function testActionConfig()
    {
        $configFileName = $this->configFileName;
        $this->runMessageControllerAction('config', [$configFileName]);
        $this->assertTrue(file_exists($configFileName), 'Unable to create config file template!');
    }

    public function testConfigFileNotExist()
    {
        $this->setExpectedException('yii\\console\\Exception');
        $this->runMessageControllerAction('extract', ['not_existing_file.php']);
    }

    public function testCreateTranslation()
    {
        $language = 'en';

        $category = 'test_category1';
        $message = 'test message';
        $sourceFileContent = "Yii::t('{$category}', '{$message}')";
        $this->createSourceFile($sourceFileContent);

        $this->composeConfigFile([
            'languages' => [$language],
            'sourcePath' => $this->sourcePath,
            'messagePath' => $this->messagePath,
        ]);
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $this->assertTrue(file_exists($this->messagePath . DIRECTORY_SEPARATOR . $language), 'No language dir created!');
        $messageFileName = $this->messagePath . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $category . '.php';
        $this->assertTrue(file_exists($messageFileName), 'No message file created!');
        $messages = require($messageFileName);
        $this->assertTrue(is_array($messages), 'Unable to compose messages!');
        $this->assertTrue(array_key_exists($message, $messages), 'Source message is missing!');
    }

    /**
     * @depends testCreateTranslation
     */
    public function testNothingNew()
    {
        $language = 'en';

        $category = 'test_category2';
        $message = 'test message';
        $sourceFileContent = "Yii::t('{$category}', '{$message}')";
        $this->createSourceFile($sourceFileContent);

        $this->composeConfigFile([
            'languages' => [$language],
            'sourcePath' => $this->sourcePath,
            'messagePath' => $this->messagePath,
        ]);
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messageFileName = $this->messagePath . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $category . '.php';

        // check file not overwritten:
        $messageFileContent = file_get_contents($messageFileName);
        $messageFileContent .= '// some not generated by command content';
        file_put_contents($messageFileName, $messageFileContent);

        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $this->assertEquals($messageFileContent, file_get_contents($messageFileName));
    }

    /**
     * @depends testCreateTranslation
     */
    public function testMerge()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Can not test on HHVM because modified files can not be reloaded.');
        }

        $language = 'en';
        $category = 'test_category3';
        $messageFileName = $language . DIRECTORY_SEPARATOR . $category . '.php';

        $existingMessage = 'test existing message';
        $existingMessageContent = 'test existing message content';
        $this->createMessageFile($messageFileName, [
            $existingMessage => $existingMessageContent
        ]);

        $newMessage = 'test new message';
        $sourceFileContent = "Yii::t('{$category}', '{$existingMessage}')";
        $sourceFileContent .= "Yii::t('{$category}', '{$newMessage}')";
        $this->createSourceFile($sourceFileContent);

        $this->composeConfigFile([
            'languages' => [$language],
            'sourcePath' => $this->sourcePath,
            'messagePath' => $this->messagePath,
            'overwrite' => true,
        ]);
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = require($this->messagePath . DIRECTORY_SEPARATOR . $messageFileName);
        $this->assertTrue(array_key_exists($newMessage, $messages), 'Unable to add new message!');
        $this->assertTrue(array_key_exists($existingMessage, $messages), 'Unable to keep existing message!');
        $this->assertEquals('', $messages[$newMessage], 'Wrong new message content!');
        $this->assertEquals($existingMessageContent, $messages[$existingMessage], 'Unable to keep existing message content!');
    }

    /**
     * @depends testMerge
     */
    public function testNoLongerNeedTranslation()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Can not test on HHVM because modified files can not be reloaded.');
        }

        $language = 'en';
        $category = 'test_category4';
        $messageFileName = $language . DIRECTORY_SEPARATOR . $category . '.php';

        $oldMessage = 'test old message';
        $oldMessageContent = 'test old message content';
        $this->createMessageFile($messageFileName, [
            $oldMessage => $oldMessageContent
        ]);

        $sourceFileContent = "Yii::t('{$category}', 'some new message')";
        $this->createSourceFile($sourceFileContent);

        $this->composeConfigFile([
            'languages' => [$language],
            'sourcePath' => $this->sourcePath,
            'messagePath' => $this->messagePath,
            'overwrite' => true,
            'removeUnused' => false,
        ]);
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = require($this->messagePath . DIRECTORY_SEPARATOR . $messageFileName);

        $this->assertTrue(array_key_exists($oldMessage, $messages), 'No longer needed message removed!');
        $this->assertEquals('@@' . $oldMessageContent . '@@', $messages[$oldMessage], 'No longer needed message content does not marked properly!');
    }

    /**
     * @depends testMerge
     */
    public function testMergeWithContentZero()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Can not test on HHVM because modified files can not be reloaded.');
        }

        $language = 'en';
        $category = 'test_category5';
        $messageFileName = $language . DIRECTORY_SEPARATOR . $category . '.php';

        $zeroMessage = 'test zero message';
        $zeroMessageContent = '0';
        $falseMessage = 'test false message';
        $falseMessageContent = 'false';
        $this->createMessageFile($messageFileName, [
            $zeroMessage => $zeroMessageContent,
            $falseMessage => $falseMessageContent,
        ]);

        $newMessage = 'test new message';
        $sourceFileContent = "Yii::t('{$category}', '{$zeroMessage}')";
        $sourceFileContent .= "Yii::t('{$category}', '{$falseMessage}')";
        $sourceFileContent .= "Yii::t('{$category}', '{$newMessage}')";
        $this->createSourceFile($sourceFileContent);

        $this->composeConfigFile([
            'languages' => [$language],
            'sourcePath' => $this->sourcePath,
            'messagePath' => $this->messagePath,
            'overwrite' => true,
        ]);
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messages = require($this->messagePath . DIRECTORY_SEPARATOR . $messageFileName);
        $this->assertTrue($zeroMessageContent === $messages[$zeroMessage], 'Message content "0" is lost!');
        $this->assertTrue($falseMessageContent === $messages[$falseMessage], 'Message content "false" is lost!');
    }

    /**
     * @depends testCreateTranslation
     */
    public function testMultiplyTranslators()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Can not test on HHVM because modified files can not be reloaded.');
        }

        $language = 'en';
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

        $this->composeConfigFile([
            'languages' => [$language],
            'sourcePath' => $this->sourcePath,
            'messagePath' => $this->messagePath,
            'translator' => $translators,
        ]);
        $this->runMessageControllerAction('extract', [$this->configFileName]);

        $messageFileName = $this->messagePath . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $category . '.php';
        $messages = require($messageFileName);

        foreach ($sourceMessages as $sourceMessage) {
            $this->assertTrue(array_key_exists($sourceMessage, $messages));
        }
    }
}
