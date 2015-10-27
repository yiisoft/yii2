<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use yii\base\Event;
use yii\i18n\I18N;
use yii\i18n\PhpMessageSource;
use yiiunit\TestCase;

/**
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 * @group i18n
 */
class I18NTest extends TestCase
{
    /**
     * @var I18N
     */
    public $i18n;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->i18n = new I18N([
            'translations' => [
                'test' => new PhpMessageSource([
                    'basePath' => '@yiiunit/data/i18n/messages',
                ])
            ]
        ]);
    }

    public function testTranslate()
    {
        $msg = 'The dog runs fast.';

        // source = target. Should be returned as is.
        $this->assertEquals('The dog runs fast.', $this->i18n->translate('test', $msg, [], 'en'));

        // exact match
        $this->assertEquals('Der Hund rennt schnell.', $this->i18n->translate('test', $msg, [], 'de-DE'));

        // fallback to just language code with absent exact match
        $this->assertEquals('Собака бегает быстро.', $this->i18n->translate('test', $msg, [], 'ru-RU'));

        // fallback to just langauge code with present exact match
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
    }

    public function testDefaultSource()
    {
        $i18n = new I18N([
            'translations' => [
                '*' => new PhpMessageSource([
                'basePath' => '@yiiunit/data/i18n/messages',
                    'fileMap' => [
                        'test' => 'test.php',
                        'foo' => 'test.php',
                    ],
                ])
            ]
        ]);

        $msg = 'The dog runs fast.';

        // source = target. Should be returned as is.
        $this->assertEquals($msg, $i18n->translate('test', $msg, [], 'en'));

        // exact match
        $this->assertEquals('Der Hund rennt schnell.', $i18n->translate('test', $msg, [], 'de-DE'));
        $this->assertEquals('Der Hund rennt schnell.', $i18n->translate('foo', $msg, [], 'de-DE'));
        $this->assertEquals($msg, $i18n->translate('bar', $msg, [], 'de-DE'));

        // fallback to just language code with absent exact match
        $this->assertEquals('Собака бегает быстро.', $i18n->translate('test', $msg, [], 'ru-RU'));

        // fallback to just langauge code with present exact match
        $this->assertEquals('Hallo Welt!', $i18n->translate('test', 'Hello world!', [], 'de-DE'));
    }

    public function testTranslateParams()
    {
        $msg = 'His speed is about {n} km/h.';
        $params = ['n' => 42];
        $this->assertEquals('His speed is about 42 km/h.', $this->i18n->translate('test', $msg, $params, 'en-US'));
        $this->assertEquals('Seine Geschwindigkeit beträgt 42 km/h.', $this->i18n->translate('test', $msg, $params, 'de-DE'));
    }

    public function testTranslateParams2()
    {
        if (!extension_loaded("intl")) {
            $this->markTestSkipped("intl not installed. Skipping.");
        }
        $msg = 'His name is {name} and his speed is about {n, number} km/h.';
        $params = [
            'n' => 42,
            'name' => 'DA VINCI', // http://petrix.com/dognames/d.html
        ];
        $this->assertEquals('His name is DA VINCI and his speed is about 42 km/h.', $this->i18n->translate('test', $msg, $params, 'en-US'));
        $this->assertEquals('Er heißt DA VINCI und ist 42 km/h schnell.', $this->i18n->translate('test', $msg, $params, 'de-DE'));
    }

    public function testSpecialParams()
    {
        $msg = 'His speed is about {0} km/h.';

        $this->assertEquals('His speed is about 0 km/h.', $this->i18n->translate('test', $msg, 0, 'en-US'));
        $this->assertEquals('His speed is about 42 km/h.', $this->i18n->translate('test', $msg, 42, 'en-US'));
        $this->assertEquals('His speed is about {0} km/h.', $this->i18n->translate('test', $msg, null, 'en-US'));
        $this->assertEquals('His speed is about {0} km/h.', $this->i18n->translate('test', $msg, [], 'en-US'));
    }

    /**
     * When translation is missing source language should be used for formatting.
     * https://github.com/yiisoft/yii2/issues/2209
     */
    public function testMissingTranslationFormatting()
    {
        $this->assertEquals('1 item', $this->i18n->translate('test', '{0, number} {0, plural, one{item} other{items}}', 1, 'hu'));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/7093
     */
    public function testRussianPlurals()
    {
        $this->assertEquals('На диване лежит 6 кошек!', $this->i18n->translate('test', 'There {n, plural, =0{no cats} =1{one cat} other{are # cats}} on lying on the sofa!', ['n' => 6], 'ru'));
    }

    public function testUsingSourceLanguageForMissingTranslation()
    {
        \Yii::$app->sourceLanguage = 'ru';
        \Yii::$app->language = 'en';

        $msg = '{n, plural, =0{Нет комментариев} =1{# комментарий} one{# комментарий} few{# комментария} many{# комментариев} other{# комментария}}';
        $this->assertEquals('5 комментариев', \Yii::t('app', $msg, ['n' => 5]));
        $this->assertEquals('3 комментария', \Yii::t('app', $msg, ['n' => 3]));
        $this->assertEquals('1 комментарий', \Yii::t('app', $msg, ['n' => 1]));
        $this->assertEquals('21 комментарий', \Yii::t('app', $msg, ['n' => 21]));
        $this->assertEquals('Нет комментариев', \Yii::t('app', $msg, ['n' => 0]));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/2519
     */
    public function testMissingTranslationEvent()
    {
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        $this->assertEquals('Missing translation message.', $this->i18n->translate('test', 'Missing translation message.', [], 'de-DE'));
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));

        Event::on(PhpMessageSource::className(), PhpMessageSource::EVENT_MISSING_TRANSLATION, function ($event) {});
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        $this->assertEquals('Missing translation message.', $this->i18n->translate('test', 'Missing translation message.', [], 'de-DE'));
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        Event::off(PhpMessageSource::className(), PhpMessageSource::EVENT_MISSING_TRANSLATION);

        Event::on(PhpMessageSource::className(), PhpMessageSource::EVENT_MISSING_TRANSLATION, function ($event) {
            if ($event->message == 'New missing translation message.') {
                $event->translatedMessage = 'TRANSLATION MISSING HERE!';
            }
        });
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        $this->assertEquals('Another missing translation message.', $this->i18n->translate('test', 'Another missing translation message.', [], 'de-DE'));
        $this->assertEquals('Missing translation message.', $this->i18n->translate('test', 'Missing translation message.', [], 'de-DE'));
        $this->assertEquals('TRANSLATION MISSING HERE!', $this->i18n->translate('test', 'New missing translation message.', [], 'de-DE'));
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        Event::off(PhpMessageSource::className(), PhpMessageSource::EVENT_MISSING_TRANSLATION);
    }
}
