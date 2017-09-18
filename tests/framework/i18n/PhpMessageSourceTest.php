<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use Yii;
use yii\helpers\FileHelper;
use yii\i18n\I18N;
use yii\i18n\PhpMessageSource;

/**
 * @group i18n
 */
class PhpMessageSourceTest extends I18NTest
{
    protected $testFilePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

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

    /**
     * {@inheritdoc}
     */
    protected function setI18N()
    {
        $this->i18n = new I18N([
            'translations' => [
                'test' => [
                    'class' => $this->getMessageSourceClass(),
                    'basePath' => '@yiiunit/data/i18n/messages',
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMessageSourceClass()
    {
        return PhpMessageSource::class;
    }

    public function testSave()
    {
        $messageSource = new PhpMessageSource([
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