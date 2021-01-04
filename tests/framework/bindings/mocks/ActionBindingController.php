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
        return $request;
    }

    public function actionActiveRecord(Post $model)
    {
        return $model;
    }

    public function actionModel(PostModel $model)
    {
        return $model;
    }

    public function actionActiveDataFilter(ActiveDataFilter $model)
    {
        return $model;
    }

    public function actionDataFilter(DataFilter $model)
    {
        return $model;
    }
}
