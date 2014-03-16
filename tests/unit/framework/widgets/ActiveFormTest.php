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
        $this->mockApplication();
    }

    public function testBooleanAttributes()
    {
        $o = ['template' => '{input}'];

        $model = new DynamicModel(['name']);
        ob_start();
        $form = new ActiveForm(['action' => './']);
        ob_end_clean();

        $this->assertEquals(<<<EOF
<div class="form-group field-dynamicmodel-name">
<input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" required>
</div>
EOF
, (string) $form->field($model, 'name', $o)->input('email', ['required' => true]));

        $this->assertEquals(<<<EOF
<div class="form-group field-dynamicmodel-name">
<input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]">
</div>
EOF
            , (string) $form->field($model, 'name', $o)->input('email', ['required' => false]));


        $this->assertEquals(<<<EOF
<div class="form-group field-dynamicmodel-name">
<input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" required="test">
</div>
EOF
            , (string) $form->field($model, 'name', $o)->input('email', ['required' => 'test']));

    }
}
