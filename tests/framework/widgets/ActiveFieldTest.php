<?php

namespace yiiunit\framework\widgets;

use Yii;
use yii\widgets\ActiveField;
use yii\base\DynamicModel;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\web\AssetManager;

/**
 * @author Nelson J Morais <njmorais@gmail.com>
 *
 * @group widgets
 */
class ActiveFieldTest extends \yiiunit\TestCase
{
    /**
     * @var ActiveField
     */
    private $activeField;
    /**
     * @var DynamicModel
     */
    private $helperModel;
    /**
     * @var ActiveForm
     */
    private $helperForm;
    private $attributeName = 'attributeName';

    protected function setUp()
    {
        parent::setUp();
        // dirty way to have Request object not throwing exception when running testHomeLinkNull()
        $_SERVER['SCRIPT_FILENAME'] = "index.php";
        $_SERVER['SCRIPT_NAME'] = "index.php";

        $this->mockWebApplication();

        Yii::setAlias('@testWeb', '/');
        Yii::setAlias('@testWebRoot', '@yiiunit/data/web');

        $this->helperModel = new DynamicModel(['attributeName']);
        ob_start();
        $this->helperForm = new ActiveForm(['action' => '/something']);
        ob_end_clean();

        $this->activeField = new ActiveFieldExtend(true);
        $this->activeField->form = $this->helperForm;
        $this->activeField->form->setView($this->getView());
        $this->activeField->model = $this->helperModel;
        $this->activeField->attribute = $this->attributeName;
    }

    public function testRenderNoContent()
    {
        $expectedValue = <<<EOD
<div class="form-group field-dynamicmodel-attributename">
<label class="control-label" for="dynamicmodel-attributename">Attribute Name</label>
<input type="text" id="dynamicmodel-attributename" class="form-control" name="DynamicModel[{$this->attributeName}]">

<div class="help-block"></div>
</div>
EOD;

        $actualValue = $this->activeField->render();
        $this->assertEqualsWithoutLE($expectedValue, $actualValue);
    }

    /**
     * @todo    discuss|review  Expected HTML shouldn't be wrapped only by the $content?
     */
    public function testRenderWithCallableContent()
    {
        // field will be the html of the model's attribute wrapped with the return string below.
        $field = $this->attributeName;
        $content = function($field) {
            return "<div class=\"custom-container\"> $field </div>";
        };

        $expectedValue = <<<EOD
<div class="form-group field-dynamicmodel-attributename">
<div class="custom-container"> <div class="form-group field-dynamicmodel-attributename">
<label class="control-label" for="dynamicmodel-attributename">Attribute Name</label>
<input type="text" id="dynamicmodel-attributename" class="form-control" name="DynamicModel[{$this->attributeName}]">

<div class="help-block"></div>
</div> </div>
</div>
EOD;

        $actualValue = $this->activeField->render($content);
        $this->assertEqualsWithoutLE($expectedValue, $actualValue);
    }

    public function testBeginHasErrors()
    {
        $this->helperModel->addError($this->attributeName, "Error Message");

        $expectedValue = '<div class="form-group field-dynamicmodel-attributename has-error">';
        $actualValue = $this->activeField->begin();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testBeginAttributeIsRequired()
    {
        $this->helperModel->addRule($this->attributeName, 'required');

        $expectedValue = '<div class="form-group field-dynamicmodel-attributename required">';
        $actualValue = $this->activeField->begin();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testBeginHasErrorAndRequired()
    {
        $this->helperModel->addError($this->attributeName, "Error Message");
        $this->helperModel->addRule($this->attributeName, 'required');

        $expectedValue = '<div class="form-group field-dynamicmodel-attributename required has-error">';
        $actualValue = $this->activeField->begin();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testEnd()
    {
        $expectedValue = '</div>';
        $actualValue = $this->activeField->end();

        $this->assertEquals($expectedValue, $actualValue);

        // other tag
        $expectedValue = "</article>";
        $this->activeField->options['tag'] = 'article';
        $actualValue = $this->activeField->end();

        $this->assertTrue($actualValue === $expectedValue);
    }

    public function testLabel()
    {
        $expectedValue = '<label class="control-label" for="dynamicmodel-attributename">Attribute Name</label>';
        $this->activeField->label();

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);

        // label = false
        $expectedValue = '';
        $this->activeField->label(false);

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);

        // $label = 'Label Name'
        $label = 'Label Name';
        $expectedValue = <<<EOT
<label class="control-label" for="dynamicmodel-attributename">{$label}</label>
EOT;
        $this->activeField->label($label);

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);
    }


    public function testError()
    {
        $expectedValue = '<label class="control-label" for="dynamicmodel-attributename">Attribute Name</label>';
        $this->activeField->label();

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);

        // label = false
        $expectedValue = '';
        $this->activeField->label(false);

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);

        // $label = 'Label Name'
        $label = 'Label Name';
        $expectedValue = <<<EOT
<label class="control-label" for="dynamicmodel-attributename">{$label}</label>
EOT;
        $this->activeField->label($label);

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);
    }

    public function testHint()
    {
        $expectedValue = '<div class="hint-block">Hint Content</div>';
        $this->activeField->hint('Hint Content');

        $this->assertEquals($expectedValue, $this->activeField->parts['{hint}']);
    }

    public function testInput()
    {
        $expectedValue = <<<EOD
<input type="password" id="dynamicmodel-attributename" class="form-control" name="DynamicModel[attributeName]">
EOD;
        $this->activeField->input("password");

        $this->assertEquals($expectedValue, $this->activeField->parts['{input}']);

        // with options
        $expectedValue = <<<EOD
<input type="password" id="dynamicmodel-attributename" class="form-control" name="DynamicModel[attributeName]" weird="value">
EOD;
        $this->activeField->input("password", ['weird' => 'value']);

        $this->assertEquals($expectedValue, $this->activeField->parts['{input}']);
    }

    public function testTextInput()
    {
        $expectedValue = <<<EOD
<input type="text" id="dynamicmodel-attributename" class="form-control" name="DynamicModel[attributeName]">
EOD;
        $this->activeField->textInput();
        $this->assertEquals($expectedValue, $this->activeField->parts['{input}']);
    }

    public function testHiddenInput()
    {
        $expectedValue = <<<EOD
<input type="hidden" id="dynamicmodel-attributename" class="form-control" name="DynamicModel[attributeName]">
EOD;
        $this->activeField->hiddenInput();
        $this->assertEquals($expectedValue, $this->activeField->parts['{input}']);
    }

    public function testListBox()
    {
        $expectedValue = <<<EOD
<input type="hidden" name="DynamicModel[attributeName]" value=""><select id="dynamicmodel-attributename" class="form-control" name="DynamicModel[attributeName]" size="4">
<option value="1">Item One</option>
<option value="2">Item 2</option>
</select>
EOD;
        $this->activeField->listBox(["1" => "Item One", "2" => "Item 2"]);
        $this->assertEqualsWithoutLE($expectedValue, $this->activeField->parts['{input}']);

        // https://github.com/yiisoft/yii2/issues/8848
        $expectedValue = <<<EOD
<input type="hidden" name="DynamicModel[attributeName]" value=""><select id="dynamicmodel-attributename" class="form-control" name="DynamicModel[attributeName]" size="4">
<option value="value1" disabled>Item One</option>
<option value="value2" label="value 2">Item 2</option>
</select>
EOD;
        $this->activeField->listBox(["value1" => "Item One", "value2" => "Item 2"], ['options' => [
            'value1' => ['disabled' => true],
            'value2' => ['label' => 'value 2'],
        ]]);
        $this->assertEqualsWithoutLE($expectedValue, $this->activeField->parts['{input}']);

        $expectedValue = <<<EOD
<input type="hidden" name="DynamicModel[attributeName]" value=""><select id="dynamicmodel-attributename" class="form-control" name="DynamicModel[attributeName]" size="4">
<option value="value1" disabled>Item One</option>
<option value="value2" selected label="value 2">Item 2</option>
</select>
EOD;
        $this->activeField->model->{$this->attributeName} = 'value2';
        $this->activeField->listBox(["value1" => "Item One", "value2" => "Item 2"], ['options' => [
            'value1' => ['disabled' => true],
            'value2' => ['label' => 'value 2'],
        ]]);
        $this->assertEqualsWithoutLE($expectedValue, $this->activeField->parts['{input}']);
    }

    public function testGetClientOptionsReturnEmpty()
    {
        // setup: we want the real deal here!
        $this->activeField->setClientOptionsEmpty(false);

        // expected empty
        $actualValue = $this->activeField->getClientOptions();
        $this->assertTrue(empty($actualValue) === true);
    }

    public function testGetClientOptionsWithActiveAttributeInScenario()
    {
        $this->activeField->setClientOptionsEmpty(false);

        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');
        $this->activeField->form->enableClientValidation = false;

        // expected empty
        $actualValue = $this->activeField->getClientOptions();
        $this->assertTrue(empty($actualValue) === true);

    }

    public function testGetClientOptionsClientValidation()
    {
        $this->activeField->setClientOptionsEmpty(false);

        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');
        $this->activeField->enableClientValidation = true;
        $actualValue = $this->activeField->getClientOptions();
        $expectedJsExpression = "function (attribute, value, messages, deferred, \$form) {return true;}";
        $this->assertEquals($expectedJsExpression, $actualValue['validate']);

        $this->assertTrue(!isset($actualValue['validateOnChange']));
        $this->assertTrue(!isset($actualValue['validateOnBlur']));
        $this->assertTrue(!isset($actualValue['validateOnType']));
        $this->assertTrue(!isset($actualValue['validationDelay']));
        $this->assertTrue(!isset($actualValue['enableAjaxValidation']));

        $this->activeField->validateOnChange = $expectedValidateOnChange = false;
        $this->activeField->validateOnBlur = $expectedValidateOnBlur = false;
        $this->activeField->validateOnType = $expectedValidateOnType = true;
        $this->activeField->validationDelay = $expectedValidationDelay = 100;
        $this->activeField->enableAjaxValidation = $expectedEnableAjaxValidation = true;

        $actualValue = $this->activeField->getClientOptions();

        $this->assertTrue($expectedValidateOnChange === $actualValue['validateOnChange']);
        $this->assertTrue($expectedValidateOnBlur === $actualValue['validateOnBlur']);
        $this->assertTrue($expectedValidateOnType === $actualValue['validateOnType']);
        $this->assertTrue($expectedValidationDelay === $actualValue['validationDelay']);
        $this->assertTrue($expectedEnableAjaxValidation === $actualValue['enableAjaxValidation']);
    }

    public function testGetClientOptionsValidatorWhenClientSet()
    {
        $this->activeField->setClientOptionsEmpty(false);
        $this->activeField->enableAjaxValidation = true;
        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');

        foreach($this->activeField->model->validators as $validator) {
            $validator->whenClient = "function (attribute, value) { return 'yii2' == 'yii2'; }"; // js
        }

        $actualValue = $this->activeField->getClientOptions();
        $expectedJsExpression = "function (attribute, value, messages, deferred, \$form) {if ((function (attribute, value) "
            . "{ return 'yii2' == 'yii2'; })(attribute, value)) { return true; }}";

        $this->assertEquals($expectedJsExpression, $actualValue['validate']->expression);
    }

    /**
     * Helper methods
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

/**
 * Helper Classes
 */
class ActiveFieldExtend extends ActiveField
{
    private $getClientOptionsEmpty;

    public function __construct($getClientOptionsEmpty = true)
    {
        $this->getClientOptionsEmpty = $getClientOptionsEmpty;
    }

    public function setClientOptionsEmpty($value)
    {
        $this->getClientOptionsEmpty = (bool) $value;
    }

    /**
     * Useful to test other methods from ActiveField, that call ActiveField::getClientOptions()
     * but it's return value is not relevant for the test being run.
     */
    public function getClientOptions()
    {
        return ($this->getClientOptionsEmpty) ? [] : parent::getClientOptions();
    }
}

class TestValidator extends \yii\validators\Validator
{

    public function clientValidateAttribute($object, $attribute, $view)
    {
        return "return true;";
    }

    public function setWhenClient($js)
    {
        $this->whenClient = $js;
    }
}
