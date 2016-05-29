<?php
namespace yiiunit\framework\web;

use Yii;
use yiiunit\TestCase;

class ApplicationTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    public function testName()
    {
        $appName = 'testapp2';
        \Yii::$app->setName($appName);

        $this->assertEquals($appName, \Yii::$app->name);
        $this->assertEquals($appName, \Yii::$app->getName());
    }

    public function testCharset()
    {
        $charset = 'ISO-8859-1';
        \Yii::$app->setCharset($charset);

        $this->assertEquals($charset, \Yii::$app->charset);
        $this->assertEquals($charset, \Yii::$app->getCharset());
    }


    public function testLanguage()
    {
        $language = 'zh-CN';
        \Yii::$app->setLanguage($language);

        $this->assertEquals($language, \Yii::$app->language);
        $this->assertEquals($language, \Yii::$app->getLanguage());
    }

    public function testSourceLanguage()
    {
        $sourceLanguage = 'zh-CN';
        \Yii::$app->setSourceLanguage($sourceLanguage);

        $this->assertEquals($sourceLanguage, \Yii::$app->sourceLanguage);
        $this->assertEquals($sourceLanguage, \Yii::$app->getSourceLanguage());
    }
}
