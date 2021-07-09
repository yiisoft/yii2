<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings\mocks;

class Composite
{
    public \yii\web\Request $request;
    public Circle $circle;
    public \yii\data\ActiveDataFilter $filter;
}
