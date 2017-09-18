<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use Yii;
use yii\helpers\FileHelper;
use yii\i18n\GettextMessageSource;
use yiiunit\TestCase;

/**
 * @group i18n
 */
class GettextMessageSourceTest extends TestCase
{
    protected $testFilePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();

        $this->testFilePath = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'test-i18n-messages';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        FileHelper::removeDirectory($this->testFilePath);
    }

    public function testLoadMessages()
    {
        $this->markTestIncomplete();
    }

    public function testSave()
    {
        $messageSource = new GettextMessageSource([
            'basePath' => $this->testFilePath
        ]);

        $messages = [
            'message 1' => 'message 1 translation',
            'message 2' => 'message 2 translation',
        ];
        $this->assertSame(2, $messageSource->save('app', 'en', $messages));
        $this->assertSame($messages, $messageSource->loadMessages('app', 'en'));

        $this->assertSame(0, $messageSource->save('app', 'en', $messages));
        $this->assertSame($messages, $messageSource->loadMessages('app', 'en'));

        $this->assertSame(0, $messageSource->save('app', 'en', [
            'message 1' => '',
            'message 2' => '',
        ]));
        $this->assertSame($messages, $messageSource->loadMessages('app', 'en'));

        $this->assertSame(2, $messageSource->save('app', 'en', [
            'message 2' => 'new message 2',
        ], ['markUnused' => true]));
        $expectedMessages = [
            'message 1' => '@@message 1 translation@@',
            'message 2' => 'new message 2',
        ];
        $this->assertSame($expectedMessages, $messageSource->loadMessages('app', 'en'));

        $this->assertSame(2, $messageSource->save('app', 'en', [
            'message 2' => 'another new message 2',
        ], ['removeUnused' => true]));
        $expectedMessages = [
            'message 2' => 'another new message 2',
        ];
        $this->assertSame($expectedMessages, $messageSource->loadMessages('app', 'en'));
    }
}
