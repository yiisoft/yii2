<?php
namespace yiiunit\framework;

use Yii;
use yii\di\Container;
use yiiunit\data\base\Singer;
use yiiunit\TestCase;

/**
 * BaseYiiTest
 * @group base
 */
class BaseYiiTest extends TestCase
{
    public $aliases;

    protected function setUp()
    {
        parent::setUp();
        $this->aliases = Yii::$aliases;
    }

    protected function tearDown()
    {
        parent::tearDown();
        Yii::$aliases = $this->aliases;
    }

    public function testAlias()
    {
        $this->assertEquals(YII2_PATH, Yii::getAlias('@yii'));

        Yii::$aliases = [];
        $this->assertFalse(Yii::getAlias('@yii', false));

        Yii::setAlias('@yii', '/yii/framework');
        $this->assertEquals('/yii/framework', Yii::getAlias('@yii'));
        $this->assertEquals('/yii/framework/test/file', Yii::getAlias('@yii/test/file'));
        Yii::setAlias('@yii/gii', '/yii/gii');
        $this->assertEquals('/yii/framework', Yii::getAlias('@yii'));
        $this->assertEquals('/yii/framework/test/file', Yii::getAlias('@yii/test/file'));
        $this->assertEquals('/yii/gii', Yii::getAlias('@yii/gii'));
        $this->assertEquals('/yii/gii/file', Yii::getAlias('@yii/gii/file'));

        Yii::setAlias('@tii', '@yii/test');
        $this->assertEquals('/yii/framework/test', Yii::getAlias('@tii'));

        Yii::setAlias('@yii', null);
        $this->assertFalse(Yii::getAlias('@yii', false));
        $this->assertEquals('/yii/gii/file', Yii::getAlias('@yii/gii/file'));

        Yii::setAlias('@some/alias', '/www');
        $this->assertEquals('/www', Yii::getAlias('@some/alias'));

        Yii::setAlias('@foo', '@bar');
        Yii::setAlias('@foo/ccc', '@bar/ccc');
        Yii::setAlias('@foo/ccc/ddd', '@bar/ccc/bbb');

        Yii::setAlias('@baz', '@bar/ddd');
        Yii::setAlias('@baz/eee', '@bar/ddd/eee');
        Yii::setAlias('@baz/fff', '@bar/ddd/eee/fff');

        Yii::setAlias('@bar/ddd', '/bbb');
        Yii::setAlias('@bar', '/aaa');

        $this->assertEquals('/aaa', Yii::getAlias('@foo'));
        $this->assertEquals('/aaa/ccc', Yii::getAlias('@foo/ccc'));
        $this->assertEquals('/aaa/ccc/bbb', Yii::getAlias('@foo/ccc/ddd'));
        $this->assertEquals('/aaa/ccc/bbb/eee', Yii::getAlias('@foo/ccc/ddd/eee'));

        $this->assertEquals('/bbb', Yii::getAlias('@baz'));
        $this->assertEquals('/bbb/eee', Yii::getAlias('@baz/eee'));
        $this->assertEquals('/bbb/eee/fff', Yii::getAlias('@baz/fff'));
        $this->assertEquals('/bbb/eee/fff/ggg', Yii::getAlias('@baz/fff/ggg'));

        $this->assertEquals('/aaa', Yii::getAlias('@bar'));
        $this->assertEquals('/aaa/bbb', Yii::getAlias('@bar/bbb'));

        Yii::setAlias('@bar/ddd', '/iii');
        Yii::setAlias('@bar', '/kkk');

        $this->assertEquals('/kkk', Yii::getAlias('@foo'));
        $this->assertEquals('/iii/eee', Yii::getAlias('@baz/eee'));
    }

    public function testGetVersion()
    {
        $this->assertTrue((boolean) preg_match('~\d+\.\d+(?:\.\d+)?(?:-\w+)?~', \Yii::getVersion()));
    }

    public function testPowered()
    {
        $this->assertTrue(is_string(Yii::powered()));
    }

    public function testCreateObjectCallable()
    {
        Yii::$container = new Container();

        // Test passing in of normal params combined with DI params.
        $this->assertTrue(Yii::createObject(function(Singer $singer, $a) {
            return $a === 'a';
        }, ['a']));


        $singer = new Singer();
        $singer->firstName = 'Bob';
        $this->assertTrue(Yii::createObject(function(Singer $singer, $a) {
            return $singer->firstName === 'Bob';
        }, [$singer, 'a']));


        $this->assertTrue(Yii::createObject(function(Singer $singer, $a = 3) {
            return true;
        }));
    }
}
