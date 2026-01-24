<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use Yii;
use yii\web\AssetManager;
use yii\web\View;
use yii\widgets\MaskedInput;

/**
 * @group widgets
 */
class MaskedInputTest extends \yiiunit\TestCase
{
    /**
     * @var MaskedInput
     */
    private $maskedInput;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();

        Yii::setAlias('@testWeb', '/');
        Yii::setAlias('@testWebRoot', '@yiiunit/data/web');
        Yii::setAlias('@bower', '@app/../vendor/bower-asset');

        $this->maskedInput = new MaskedInput([
            'name' => 'phone',
            'mask' => '999-999-9999'
        ]);

        $this->maskedInput->setView($this->getView());

    }

    public function testMaskedInputValidState()
    {
        $this->maskedInput->name = 'phone';
        $this->maskedInput->mask = '999-999-9999';

        $this->maskedInput->init();

        ob_start();
        $this->maskedInput->run();
        $output = ob_get_clean();

        $expected = '<input type="text" id="w0" class="form-control" name="phone" data-plugin-inputmask="inputmask_7b93eb48">';

        $this->assertEqualsWithoutLE($expected, $output);
    }

    /**
     * Helper methods.
     */
    protected function getView()
    {
        $view = new View();
        $view->setAssetManager(new AssetManager([
            'basePath' => '@testWebRoot/assets',
            'baseUrl' => '@testWeb/assets',
        ]));

        return $view;
    }
}
