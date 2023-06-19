<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\base\ExitException;
use yii\filters\HostControl;
use yii\web\Controller;
use yiiunit\TestCase;

/**
 * @group filters
 */
class HostControlTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $this->mockWebApplication();
    }

    /**
     * @return array test data.
     */
    public function hostInfoValidationDataProvider()
    {
        return [
            [
                null,
                'example.com',
                true,
            ],
            [
                'example.com',
                'example.com',
                true,
            ],
            [
                ['example.com'],
                'example.com',
                true,
            ],
            [
                ['example.com'],
                'domain.com',
                false,
            ],
            [
                ['*.example.com'],
                'en.example.com',
                true,
            ],
            [
                ['*.example.com'],
                'fake.com',
                false,
            ],
            [
                function () {
                    return ['example.com'];
                },
                'example.com',
                true,
            ],
            [
                function () {
                    return ['example.com'];
                },
                'fake.com',
                false,
            ],
        ];
    }

    /**
     * @dataProvider hostInfoValidationDataProvider
     *
     * @param mixed $allowedHosts
     * @param string $host
     * @param bool $allowed
     */
    public function testFilter($allowedHosts, $host, $allowed)
    {
        $_SERVER['HTTP_HOST'] = $host;

        $filter = new HostControl();
        $filter->allowedHosts = $allowedHosts;

        $controller = new Controller('id', Yii::$app);
        $action = new Action('test', $controller);

        if ($allowed) {
            $this->assertTrue($filter->beforeAction($action));
        } else {
            ob_start();
            ob_implicit_flush(false);

            $isExit = false;

            try {
                $filter->beforeAction($action);
            } catch (ExitException $e) {
                $isExit = true;
            }

            ob_get_clean();

            $this->assertTrue($isExit);
            $this->assertEquals(404, Yii::$app->response->getStatusCode());
        }
    }

    public $denyCallBackCalled = false;

    public function testDenyCallback()
    {
        $filter = new HostControl();
        $filter->allowedHosts = ['example.com'];
        $this->denyCallBackCalled = false;
        $filter->denyCallback = function () {
            $this->denyCallBackCalled = true;
        };

        $controller = new Controller('test', Yii::$app);
        $action = new Action('test', $controller);
        $this->assertFalse($filter->beforeAction($action));
        $this->assertTrue($this->denyCallBackCalled, 'denyCallback should have been called.');
    }

    public function testDefaultHost()
    {
        $filter = new HostControl();
        $filter->allowedHosts = ['example.com'];
        $filter->fallbackHostInfo = 'http://yiiframework.com';
        $filter->denyCallback = function () {};

        $controller = new Controller('test', Yii::$app);
        $action = new Action('test', $controller);
        $filter->beforeAction($action);

        $this->assertSame('yiiframework.com', Yii::$app->getRequest()->getHostName());
    }

    public function testErrorHandlerWithDefaultHost()
    {
        $this->expectException('yii\web\NotFoundHttpException');
        $this->expectExceptionMessage('Page not found.');

        $filter = new HostControl();
        $filter->allowedHosts = ['example.com'];
        $filter->fallbackHostInfo = 'http://yiiframework.com';

        $controller = new Controller('test', Yii::$app);
        $action = new Action('test', $controller);
        $filter->beforeAction($action);
    }
}
