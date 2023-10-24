<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\filters\AjaxFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Request;
use yiiunit\TestCase;

/**
 * @group filters
 */
class AjaxFilterTest extends TestCase
{
    protected function mockRequest(bool $isAjax = false): Request
    {
        /** @var Request $request */
        $request = $this->createPartialMock(Request::class, ['getIsAjax']);
        $request->method('getIsAjax')->willReturn($isAjax);

        return $request;
    }

    public function testFilter(): void
    {
        $this->mockWebApplication();
        $controller = new Controller('id', Yii::$app);
        $action = new Action('test', $controller);
        $filter = new AjaxFilter();

        $filter->request = $this->mockRequest(true);
        $this->assertTrue($filter->beforeAction($action));

        $filter->request = $this->mockRequest(false);
        $this->expectException(BadRequestHttpException::class);
        $filter->beforeAction($action);
    }
}
