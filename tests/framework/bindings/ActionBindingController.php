<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings;

use yii\web\Request;

class ActionBindingController extends \yii\base\Controller
{
    public function actionTest(Request $request)
    {
    }
}


// class TestController extends Controller
// {

//     public function actionParams(
//         $mixed,
//         int $int,
//         float $float,
//         bool $bool,
//         DateTime $dateTime,
//         DateTimeImmutable $dateTimeImmutable
//     )
//     {
//     }

//     public function actionNoType($value)
//     {
//     }

//     public function actionBuiltin(int $int, float $float, bool $bool)
//     {
//     }

//     public function actionBuiltinNullable(?int $int, ?float $float, ?bool $bool)
//     {
//     }

//     public function actionDateTime(DateTime $dateTime, DateTimeImmutable $dateTimeImmutable)
//     {
//     }

//     public function actionDateTimeNullable(?DateTime $dateTime, ?DateTimeImmutable $dateTimeImmutable)
//     {
//     }
// }
