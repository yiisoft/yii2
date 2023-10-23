<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use yii\data\DataProviderInterface;
use yiiunit\framework\console\stubs\DummyService;
use yii\console\Controller;
use yii\console\Request;

class FakePhp71Controller extends Controller
{
    public function actionInjection($before, Request $request, $between, DummyService $dummyService, $after, Post $post = null): void
    {

    }

    public function actionNullableInjection(?Request $request, ?Post $post): void
    {
    }

    public function actionModuleServiceInjection(DataProviderInterface $dataProvider): void
    {
    }
}
