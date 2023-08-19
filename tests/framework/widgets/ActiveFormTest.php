<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yii\base\DynamicModel;
use yii\base\Widget;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @group widgets
 */
class ActiveFormTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testBooleanAttributes()
    {
        $o = ['template' => '{input}'];

        $model = new DynamicModel(['name']);
        ob_start();
        $form = ActiveForm::begin(['action' => '/something', 'enableClientScript' => false]);
        ActiveForm::end();
        ob_end_clean();

        $this->assertEqualsWithoutLE(<<<'EOF'
<div class="form-group field-dynamicmodel-name">
<input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" required>
</div>
EOF
, (string) $form->field($model, 'name', $o)->input('email', ['required' => true]));

        $this->assertEqualsWithoutLE(<<<'EOF'
<div class="form-group field-dynamicmodel-name">
<input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]">
</div>
EOF
            , (string) $form->field($model, 'name', $o)->input('email', ['required' => false]));


        $this->assertEqualsWithoutLE(<<<'EOF'
<div class="form-group field-dynamicmodel-name">
<input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" required="test">
</div>
EOF
            , (string) $form->field($model, 'name', $o)->input('email', ['required' => 'test']));
    }

    public function testIssue5356()
    {
        $o = ['template' => '{input}'];

        $model = new DynamicModel(['categories']);
        $model->categories = 1;
        ob_start();
        $form = ActiveForm::begin(['action' => '/something', 'enableClientScript' => false]);
        ActiveForm::end();
        ob_end_clean();

        // https://github.com/yiisoft/yii2/issues/5356
        $this->assertEqualsWithoutLE(<<<'EOF'
<div class="form-group field-dynamicmodel-categories">
<input type="hidden" name="DynamicModel[categories]" value=""><select id="dynamicmodel-categories" class="form-control" name="DynamicModel[categories][]" multiple size="4">
<option value="0">apple</option>
<option value="1" selected>banana</option>
<option value="2">avocado</option>
</select>
</div>
EOF
             , (string) $form->field($model, 'categories', $o)->listBox(['apple', 'banana', 'avocado'], ['multiple' => true]));
    }

    public function testOutputBuffering()
    {
        $obLevel = ob_get_level();
        ob_start();

        $model = new DynamicModel(['name']);

        $form = ActiveForm::begin(['id' => 'someform', 'action' => '/someform', 'enableClientScript' => false]);
        echo "\n" . $form->field($model, 'name') . "\n";
        ActiveForm::end();

        $content = ob_get_clean();

        $this->assertEquals($obLevel, ob_get_level(), 'Output buffers not closed correctly.');

        $this->assertEqualsWithoutLE(<<<'HTML'
<form id="someform" action="/someform" method="post">
<div class="form-group field-dynamicmodel-name">
<label class="control-label" for="dynamicmodel-name">Name</label>
<input type="text" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]">

<div class="help-block"></div>
</div>
</form>
HTML
, $content);
    }

    public function testRegisterClientScript()
    {
        $this->mockWebApplication();
        $_SERVER['REQUEST_URI'] = 'http://example.com/';

        $model = new DynamicModel(['name']);
        $model->addRule(['name'], 'required');

        $view = $this->getMockBuilder(View::className())->getMock();
        $view->method('registerJs')->with($this->matches("jQuery('#w0').yiiActiveForm([], {\"validateOnSubmit\":false});"));
        $view->method('registerAssetBundle')->willReturn(true);

        Widget::$counter = 0;
        ob_start();
        ob_implicit_flush(false);

        $form = ActiveForm::begin(['view' => $view, 'validateOnSubmit' => false]);
        $form->field($model, 'name');
        $form::end();

        // Disable clientScript will not call `View->registerJs()`
        $form = ActiveForm::begin(['view' => $view, 'enableClientScript' => false]);
        $form->field($model, 'name');
        $form::end();
        ob_get_clean();
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15536
     */
    public function testShouldTriggerInitEvent()
    {
        $initTriggered = false;
        ob_start();
        $form = ActiveForm::begin(
            [
                'action' => '/something',
                'enableClientScript' => false,
                'on init' => function () use (&$initTriggered) {
                    $initTriggered = true;
                }
            ]
        );
        ActiveForm::end();
        ob_end_clean();
        $this->assertTrue($initTriggered);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15476
     * @see https://github.com/yiisoft/yii2/issues/16892
     */
    public function testValidationStateOnInput()
    {
        $model = new DynamicModel(['name']);
        $model->addError('name', 'I have an error!');
        ob_start();
        $form = ActiveForm::begin([
            'action' => '/something',
            'enableClientScript' => false,
            'validationStateOn' => ActiveForm::VALIDATION_STATE_ON_INPUT,
        ]);
        ActiveForm::end();
        ob_end_clean();

        $this->assertEqualsWithoutLE(<<<'EOF'
<div class="form-group field-dynamicmodel-name">
<label class="control-label" for="dynamicmodel-name">Name</label>
<input type="text" id="dynamicmodel-name" class="form-control has-error" name="DynamicModel[name]" aria-invalid="true">

<div class="help-block">I have an error!</div>
</div>
EOF
        , (string) $form->field($model, 'name'));


        $this->assertEqualsWithoutLE(<<<'EOF'
<div class="form-group field-dynamicmodel-name">

<input type="hidden" name="DynamicModel[name]" value="0"><label><input type="checkbox" id="dynamicmodel-name" class="has-error" name="DynamicModel[name]" value="1" aria-invalid="true"> Name</label>

<div class="help-block">I have an error!</div>
</div>
EOF
            , (string) $form->field($model, 'name')->checkbox());

        $this->assertEqualsWithoutLE(<<<'EOF'
<div class="form-group field-dynamicmodel-name">

<input type="hidden" name="DynamicModel[name]" value="0"><label><input type="radio" id="dynamicmodel-name" class="has-error" name="DynamicModel[name]" value="1" aria-invalid="true"> Name</label>

<div class="help-block">I have an error!</div>
</div>
EOF
            , (string) $form->field($model, 'name')->radio());
    }
}
