<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\Controller;
use yiiunit\framework\di\stubs\QuxInterface;
use yiiunit\framework\web\stubs\Bar;
use yii\validators\EmailValidator;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class FakeController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionAksi1(Bar $bar, $fromGet, $other = 'default')
    {
    }

    public function actionAksi2(Bar $barBelongApp, QuxInterface $qux)
    {
    }

    public function actionAksi3(QuxInterface $quxApp)
    {
    }

    public function actionAksi4(Bar $bar, QuxInterface $quxApp, $q)
    {
        return [$bar->foo, $quxApp->quxMethod(), $q];
    }

    public function actionAksi5($q, Bar $bar, QuxInterface $quxApp)
    {
        return [$q, $bar->foo, $quxApp->quxMethod()];
    }

    public function actionAksi6($q, EmailValidator $validator)
    {
        return [$q, $validator->validate($q), $validator->validate('misbahuldmunir@gmail.com')];
    }

    public function actionShowCustomer(\yiiunit\data\ar\Customer $customer)
    {
        return $customer;
    }

    public function actionShowOrderItem(\yiiunit\data\ar\OrderItem $orderItem)
    {
        return $orderItem;
    }

    public function actionShowOptionalCustomer(\yiiunit\data\ar\Customer $customer = null) {
        return $customer;
    }
}
