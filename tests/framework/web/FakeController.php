<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Controller;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class FakeController extends Controller
{
    public $enableCsrfValidation = false;

    public function actions()
    {
        return [
            'closure' => function(array $values, $value, $other = 'default') {
                return [$values, $value, $other];
            },
        ];
    }

    public function actionAksi1($fromGet, $other = 'default')
    {
    }
}
