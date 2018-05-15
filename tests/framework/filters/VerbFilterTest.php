<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\base\ActionEvent;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Request;
use yiiunit\TestCase;

/**
 * @group filters
 */
class VerbFilterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $this->mockWebApplication();
    }

    public function testFilter()
    {
        $request = new Request();
        $this->mockWebApplication([
            'components' => [
                'request' => $request
            ],
        ]);
        $controller = new Controller('id', Yii::$app);
        $action = new Action('test', $controller);
        $filter = new VerbFilter([
            'actions' => [
                '*' => ['GET', 'POST', 'Custom'],
            ]
        ]);

        $event = new ActionEvent($action);

        $request->setMethod('GET');
        $this->assertTrue($filter->beforeAction($event));

        $request->setMethod('CUSTOM');

        try {
            $filter->beforeAction($event);
        } catch (MethodNotAllowedHttpException $exception) {
        }

        $this->assertTrue(isset($exception));
        $this->assertEquals(['GET, POST, Custom'], Yii::$app->response->getHeader('Allow'));
    }
}