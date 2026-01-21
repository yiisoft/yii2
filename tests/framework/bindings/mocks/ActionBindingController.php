<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings\mocks;

use yii\data\ActiveDataFilter;
use yii\data\DataFilter;
use yii\web\Request;

class ActionBindingController extends \yii\base\Controller
{
    public function actionTest(Request $request)
    {
    }

    public function actionActiveRecord(Post $model)
    {
    }

    public function actionModel(PostModel $model)
    {
    }

    public function actionActiveDataFilter(ActiveDataFilter $model)
    {
    }

    public function actionDataFilter(DataFilter $model)
    {
    }

    public function actionPoint(Point $model)
    {
    }

    public function actionCircle(Circle $model)
    {
    }

    public function actionComposite(Composite $model)
    {
    }
}
