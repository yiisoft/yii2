<?php

namespace yii\base;

use Yii;
use yiiunit\TestCase;
use yii\base\Theme;

class ThemeTest extends TestCase
{
    protected function setUp()
    {
        $config = ['aliases' => ['@web' => '']];
        $this->mockWebApplication($config);
    }
    
    public function testSetBaseUrl()
    {
       $theme = new Theme(['baseUrl' => '@web/themes/basic']);
       $expected = Yii::getAlias('@web/themes/basic');

       $this->assertEquals($expected, $theme->baseUrl);
    }

    public function testGetUrlFilledBaseUrl()
    {
       $theme = new Theme(['baseUrl' => '@web/themes/basic']);
       $expected = Yii::getAlias('@web/themes/basic/js/test.js');

       $actual = $theme->getUrl('/js/test.js');

       $this->assertEquals($expected, $actual);
    }

    public function testGetUrlNotFilledBaseUrl()
    {
       $theme = new Theme(['baseUrl' => null]);

       $this->expectException('yii\base\InvalidConfigException');

       $theme->getUrl('/js/test.js');
    }

    public function testSetBasePath()
    {
       $theme = new Theme(['basePath' => '@app/framework/base/fixtures/themes/basic']);
       $expected = Yii::getAlias('@app/framework/base/fixtures/themes/basic');

       $this->assertEquals($expected, $theme->basePath);
    }

    public function testGetPathFilledBasePath()
    {
       $theme = new Theme(['basePath' => '@app/framework/base/fixtures/themes/basic']);
       $expected = Yii::getAlias('@app/framework/base/fixtures/themes/basic/img/logo.gif');

       $actual = $theme->getPath('/img/logo.gif');

       $this->assertEquals($expected, $actual);
    }

    public function testGetPathNotFilledBasePath()
    {
       $theme = new Theme(['baseUrl' => null]);

       $this->expectException('yii\base\InvalidConfigException');

       $theme->getPath('/img/logo.gif');
    }

    public function testApplyToEmptyBasePath()
    {
        $theme = new Theme(['basePath' => null]);

        $this->expectException('yii\base\InvalidConfigException');

        $theme->applyTo(null);
    }

    public function testApplyToEmptyPathMap()
    {
        $theme = new Theme(['basePath' => '@app/framework/base/fixtures/themes/basic']);
        $expected = Yii::getAlias('@app/framework/base/fixtures/themes/basic/views/site/index.php');

        $actual = $theme->applyTo(Yii::$app->basePath . '/views/site/index.php');

        $this->assertEquals($expected, $actual);
    }

    public function testApplyToFilledPathMap()
    {
        $config = [
            'pathMap' => [
                '@app/views' => '@app/framework/base/fixtures/themes/basic/views',
            ]
        ];
        $theme = new Theme($config);
        $expected = Yii::getAlias('@app/framework/base/fixtures/themes/basic/views/site/index.php');

        $actual = $theme->applyTo(Yii::$app->basePath . '/views/site/index.php');

        $this->assertEquals($expected, $actual);
    }

    public function testApplyToFilledPathMapNotExistsViewInFirstTheme()
    {
        $config = [
            'pathMap' => [
                '@app/views' => [
                    '@app/framework/base/fixtures/themes/basic/views',
                    '@app/framework/base/fixtures/themes/christmas/views',
                ],
            ],
        ];
        $theme = new Theme($config);
        $expected = Yii::getAlias('@app/framework/base/fixtures/themes/christmas/views/site/main.php');

        $actual = $theme->applyTo(Yii::$app->basePath . '/views/site/main.php');

        $this->assertEquals($expected, $actual);
    }

    public function testApplyToFilledPathMapAndInheritThemes()
    {
        $config = [
            'pathMap' => [
                '@app/views' => [
                    '@app/framework/base/fixtures/themes/christmas/views',
                    '@app/framework/base/fixtures/themes/basic/views',
                ],
            ],
        ];
        $theme = new Theme($config);
        $expected = Yii::getAlias('@app/framework/base/fixtures/themes/christmas/views/site/index.php');

        $actual = $theme->applyTo(Yii::$app->basePath . '/views/site/index.php');

        $this->assertEquals($expected, $actual);
    }

    public function testApplyToFilledPathMapAndFileNotExists()
    {
        $config = [
            'pathMap' => [
                '@app/views' => '@app/framework/base/fixtures/themes/christmas/views',
            ],
        ];
        $theme = new Theme($config);
        $expected = Yii::getAlias(Yii::$app->basePath . '/views/main/index.php');

        $actual = $theme->applyTo($expected);

        $this->assertEquals($expected, $actual);
    }
}
