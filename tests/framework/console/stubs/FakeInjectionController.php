<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\stubs;

use yii\data\DataProviderInterface;
use yii\console\Controller;
use yii\console\Request;
use yiiunit\framework\web\Post;

class FakeInjectionController extends Controller
{
    public function actionInjection(
        $before,
        Request $request,
        $between,
        DummyService $dummyService,
        ?Post $post,
        $after
    ) {
    }

    public function actionNullableInjection(?Request $request, ?Post $post): void
    {
    }

    public function actionModuleServiceInjection(DataProviderInterface $dataProvider): void
    {
    }
}
