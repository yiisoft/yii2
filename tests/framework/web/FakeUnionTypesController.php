<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Controller;

class FakeUnionTypesController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionInjection(int|string $arg, int|string $second): void
    {
    }

    public function actionArrayOrInt(array|int $foo)
    {
    }

    public function actionIntOrArray(int|array $foo)
    {
    }

    public function actionNullableUnionString(int|string|null $arg)
    {
    }

    public function actionNullableUnionWithoutString(int|float|null $arg)
    {
    }

    public function actionUnionWithObject(int|\stdClass $arg)
    {
    }

    public function actionUnionWithObjectOnly(\stdClass|\Iterator $arg)
    {
    }

    public function actionIntOrFloat(int|float $foo)
    {
    }

    public function actionNullableObjectStringUnion(\stdClass|string|null $arg)
    {
    }
}
