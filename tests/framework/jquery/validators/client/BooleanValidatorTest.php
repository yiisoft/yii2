<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\jquery\validators;

use yii\jquery\validators\client\BooleanValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group jquery
 * @group validators
 */
class BooleanValidatorTest extends TestCase
{
    public function testBuild()
    {
        $validator = new \yii\validators\BooleanValidator([
            'trueValue' => true,
            'falseValue' => false,
            'strict' => true,
        ]);

        $clientValidator = new BooleanValidator();

        $model = new FakedValidationModel();
        $model->attrA = true;
        $model->attrB = '1';
        $model->attrC = '0';
        $model->attrD = [];

        $this->assertEquals(
            'yii.validation.boolean(value, messages, {"trueValue":true,"falseValue":false,"message":"attrB must be either \"true\" or \"false\".","skipOnEmpty":1,"strict":1});',
            $clientValidator->build($validator, $model, 'attrB', new ViewStub())
        );
    }
}

class ViewStub extends View
{
    public function registerAssetBundle($name, $position = null)
    {
    }
}