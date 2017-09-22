<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\jquery\validators;

use yii\jquery\validators\client\NumberValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group jquery
 * @group validators
 */
class NumberValidatorTest extends TestCase
{
    /**
     * @see https://github.com/yiisoft/yii2/issues/3118
     */
    public function testBuild()
    {
        $view = new View(['assetBundles' => ['yii\jquery\ValidationAsset' => true]]);
        $clientValidator = new NumberValidator();
        $model = new FakedValidationModel();

        $js = $clientValidator->build(
            new \yii\validators\NumberValidator([
                'min' => 5,
                'max' => 10,
            ]),
            $model,
            'attr_number',
            $view
        );
        $this->assertContains('"min":5', $js);
        $this->assertContains('"max":10', $js);

        $js = $clientValidator->build(
            new \yii\validators\NumberValidator([
                'min' => '5',
                'max' => '10',
            ]),
            $model,
            'attr_number',
            $view
        );
        $this->assertContains('"min":5', $js);
        $this->assertContains('"max":10', $js);

        $js = $clientValidator->build(
            new \yii\validators\NumberValidator([
                'min' => 5.65,
                'max' => 13.37,
            ]),
            $model,
            'attr_number',
            $view
        );
        $this->assertContains('"min":5.65', $js);
        $this->assertContains('"max":13.37', $js);

        $js = $clientValidator->build(
            new \yii\validators\NumberValidator([
                'min' => '5.65',
                'max' => '13.37',
            ]),
            $model,
            'attr_number',
            $view
        );
        $this->assertContains('"min":5.65', $js);
        $this->assertContains('"max":13.37', $js);
    }
}