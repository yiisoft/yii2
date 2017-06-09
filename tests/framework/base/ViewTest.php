<?php
namespace yiiunit\framework\base;

use Yii;
use yii\base\View;
use yii\caching\FileCache;
use yii\helpers\FileHelper;
use yiiunit\TestCase;

/**
 * @group base
 */
class ViewTest extends TestCase
{
    /**
     * @var string path for the test files.
     */
    protected $testViewPath = '';

    public function setUp()
    {
        parent::setUp();

        $this->mockApplication();
        $this->testViewPath = Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . str_replace('\\', '_', get_class($this)) . uniqid();
        FileHelper::createDirectory($this->testViewPath);
    }

    public function tearDown()
    {
        FileHelper::removeDirectory($this->testViewPath);
        parent::tearDown();
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/13058
     */
    public function testExceptionOnRenderFile()
    {
        $view = new View();

        $exceptionViewFile = $this->testViewPath . DIRECTORY_SEPARATOR . 'exception.php';
        file_put_contents($exceptionViewFile, <<<PHP
<h1>Exception</h1>
<?php throw new Exception('Test Exception'); ?>
PHP
);
        $normalViewFile = $this->testViewPath . DIRECTORY_SEPARATOR . 'no-exception.php';
        file_put_contents($normalViewFile, <<<PHP
<h1>No Exception</h1>
PHP
        );

        $obInitialLevel = ob_get_level();

        try {
            $view->renderFile($exceptionViewFile);
        } catch (\Exception $e) {
            // shutdown exception
        }
        $view->renderFile($normalViewFile);

        $this->assertEquals($obInitialLevel, ob_get_level());
    }

    public function testRenderDynamic()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $view = new View();
        $this->assertEquals(1, $view->renderDynamic('return 1;'));
    }

    public function testRenderDynamic_DynamicPlaceholders()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $statement = "return 1;";
        $view = new View();
        if ($view->beginCache(__FUNCTION__, ['duration' => 3])) {
            $view->renderDynamic($statement);
            $view->endCache();
        }
        $this->assertEquals([
            '<![CDATA[YII-DYNAMIC-0]]>' => $statement
        ], $view->dynamicPlaceholders);
    }

    public function testRenderDynamic_StatementWithThisVariable()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $view = new View();
        $view->params['viewParam'] = 'dummy';
        $this->assertEquals($view->params['viewParam'], $view->renderDynamic('return $this->params["viewParam"];'));
    }

    public function testRenderDynamic_IncludingParams()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $view = new View();
        $this->assertEquals('YiiFramework', $view->renderDynamic('return $a . $b;', [
            'a' => 'Yii',
            'b' => 'Framework',
        ]));
    }

    public function testRenderDynamic_IncludingParams_ThrowException()
    {
        Yii::$app->set('cache', new FileCache(['cachePath' => '@yiiunit/runtime/cache']));
        $view = new View();
        try {
            $view->renderDynamic('return $a;');
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('Undefined variable: a', $message);
    }
}
