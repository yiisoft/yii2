<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\widgets;

use yii\base\DynamicModel;
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

        $this->assertEqualsWithoutLE(<<<EOF
<div class="form-group field-dynamicmodel-name">
<input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" required>
</div>
EOF
, (string) $form->field($model, 'name', $o)->input('email', ['required' => true]));

        $this->assertEqualsWithoutLE(<<<EOF
<div class="form-group field-dynamicmodel-name">
<input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]">
</div>
EOF
            , (string) $form->field($model, 'name', $o)->input('email', ['required' => false]));


        $this->assertEqualsWithoutLE(<<<EOF
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
        $this->assertEqualsWithoutLE(<<<EOF
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
        //ob_end_clean();

        $this->assertEquals($obLevel, ob_get_level(), 'Output buffers not closed correctly.');

        $this->assertEqualsWithoutLE(<<<HTML
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
}
