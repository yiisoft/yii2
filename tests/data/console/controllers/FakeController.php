<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\console\controllers;

use yii\console\Controller;

/**
 * @author Dmitry V. Alekseev <mail@alexeevdv.ru>
 * @since 2.0.16
 */
class FakeController extends Controller
{
    public $defaultAction = 'default';

    public function actionDefault()
    {
    }

    public function actionSecond()
    {
    }
}
