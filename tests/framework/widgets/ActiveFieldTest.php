<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use Yii;
use yii\base\DynamicModel;
use yii\web\AssetManager;
use yii\web\View;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;
use yii\widgets\InputWidget;

/**
 * @author Nelson J Morais <njmorais@gmail.com>
 *
 * @group widgets
 */
class ActiveFieldTest extends \yiiunit\TestCase
{
    /**
     * @var ActiveFieldExtend
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
        $_SERVER['SCRIPT_FILENAME'] = 'index.php';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $this->mockWebApplication();

        Yii::setAlias('@testWeb', '/');
        Yii::setAlias('@testWebRoot', '@yiiunit/data/web');

        $this->helperModel = new ActiveFieldTestModel(['attributeName']);
        ob_start();
        $this->helperForm = ActiveForm::begin(['action' => '/something', 'enableClientScript' => false]);
        ActiveForm::end();
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
<div class="form-group field-activefieldtestmodel-attributename">
<label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
<input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[{$this->attributeName}]">
<div class="hint-block">Hint for attributeName attribute</div>
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
        $content = function ($field) {
            return "<div class=\"custom-container\"> $field </div>";
        };

        $expectedValue = <<<EOD
<div class="form-group field-activefieldtestmodel-attributename">
<div class="custom-container"> <div class="form-group field-activefieldtestmodel-attributename">
<label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
<input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[{$this->attributeName}]">
<div class="hint-block">Hint for attributeName attribute</div>
<div class="help-block"></div>
</div> </div>
</div>
EOD;

        $actualValue = $this->activeField->render($content);
        $this->assertEqualsWithoutLE($expectedValue, $actualValue);
    }

    /**
     * @link https://github.com/yiisoft/yii2/issues/7627
     */
    public function testRenderWithCustomInputId()
    {
        $expectedValue = <<<EOD
<div class="form-group field-custom-input-id">
<label class="control-label" for="custom-input-id">Attribute Name</label>
<input type="text" id="custom-input-id" class="form-control" name="ActiveFieldTestModel[{$this->attributeName}]">
<div class="hint-block">Hint for attributeName attribute</div>
<div class="help-block"></div>
</div>
EOD;

        $this->activeField->inputOptions['id'] = 'custom-input-id';

        $actualValue = $this->activeField->render();
        $this->assertEqualsWithoutLE($expectedValue, $actualValue);
    }

    public function testBeginHasErrors()
    {
        $this->helperModel->addError($this->attributeName, 'Error Message');

        $expectedValue = '<div class="form-group field-activefieldtestmodel-attributename has-error">';
        $actualValue = $this->activeField->begin();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testBeginAttributeIsRequired()
    {
        $this->helperModel->addRule($this->attributeName, 'required');

        $expectedValue = '<div class="form-group field-activefieldtestmodel-attributename required">';
        $actualValue = $this->activeField->begin();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testBeginHasErrorAndRequired()
    {
        $this->helperModel->addError($this->attributeName, 'Error Message');
        $this->helperModel->addRule($this->attributeName, 'required');

        $expectedValue = '<div class="form-group field-activefieldtestmodel-attributename required has-error">';
        $actualValue = $this->activeField->begin();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testBegin()
    {
        $expectedValue = '<article class="form-group field-activefieldtestmodel-attributename">';
        $this->activeField->options['tag'] = 'article';
        $actualValue = $this->activeField->begin();

        $this->assertEquals($expectedValue, $actualValue);

        $expectedValue = '';
        $this->activeField->options['tag'] = null;
        $actualValue = $this->activeField->begin();

        $this->assertEquals($expectedValue, $actualValue);

        $expectedValue = '';
        $this->activeField->options['tag'] = false;
        $actualValue = $this->activeField->begin();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testEnd()
    {
        $expectedValue = '</div>';
        $actualValue = $this->activeField->end();

        $this->assertEquals($expectedValue, $actualValue);

        // other tag
        $expectedValue = '</article>';
        $this->activeField->options['tag'] = 'article';
        $actualValue = $this->activeField->end();

        $this->assertEquals($expectedValue, $actualValue);

        $expectedValue = '';
        $this->activeField->options['tag'] = false;
        $actualValue = $this->activeField->end();

        $this->assertEquals($expectedValue, $actualValue);

        $expectedValue = '';
        $this->activeField->options['tag'] = null;
        $actualValue = $this->activeField->end();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public function testLabel()
    {
        $expectedValue = '<label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>';
        $this->activeField->label();

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);

        // label = false
        $expectedValue = '';
        $this->activeField->label(false);

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);

        // $label = 'Label Name'
        $label = 'Label Name';
        $expectedValue = <<<EOT
<label class="control-label" for="activefieldtestmodel-attributename">{$label}</label>
EOT;
        $this->activeField->label($label);

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);
    }

    public function testError()
    {
        $expectedValue = '<label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>';
        $this->activeField->label();

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);

        // label = false
        $expectedValue = '';
        $this->activeField->label(false);

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);

        // $label = 'Label Name'
        $label = 'Label Name';
        $expectedValue = <<<EOT
<label class="control-label" for="activefieldtestmodel-attributename">{$label}</label>
EOT;
        $this->activeField->label($label);

        $this->assertEquals($expectedValue, $this->activeField->parts['{label}']);
    }

    public function hintDataProvider()
    {
        return [
            ['Hint Content', '<div class="hint-block">Hint Content</div>'],
            [false, ''],
            [null, '<div class="hint-block">Hint for attributeName attribute</div>'],
        ];
    }

    /**
     * @dataProvider hintDataProvider
     */
    public function testHint($hint, $expectedHtml)
    {
        $this->activeField->hint($hint);

        $this->assertEquals($expectedHtml, $this->activeField->parts['{hint}']);
    }

    public function testInput()
    {
        $expectedValue = <<<'EOD'
<input type="password" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">
EOD;
        $this->activeField->input('password');

        $this->assertEquals($expectedValue, $this->activeField->parts['{input}']);

        // with options
        $expectedValue = <<<'EOD'
<input type="password" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" weird="value">
EOD;
        $this->activeField->input('password', ['weird' => 'value']);

        $this->assertEquals($expectedValue, $this->activeField->parts['{input}']);
    }

    public function testTextInput()
    {
        $expectedValue = <<<'EOD'
<input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">
EOD;
        $this->activeField->textInput();
        $this->assertEquals($expectedValue, $this->activeField->parts['{input}']);
    }

    public function testHiddenInput()
    {
        $expectedValue = <<<'EOD'
<input type="hidden" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">
EOD;
        $this->activeField->hiddenInput();
        $this->assertEquals($expectedValue, $this->activeField->parts['{input}']);
    }

    public function testListBox()
    {
        $expectedValue = <<<'EOD'
<input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" size="4">
<option value="1">Item One</option>
<option value="2">Item 2</option>
</select>
EOD;
        $this->activeField->listBox(['1' => 'Item One', '2' => 'Item 2']);
        $this->assertEqualsWithoutLE($expectedValue, $this->activeField->parts['{input}']);

        // https://github.com/yiisoft/yii2/issues/8848
        $expectedValue = <<<'EOD'
<input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" size="4">
<option value="value1" disabled>Item One</option>
<option value="value2" label="value 2">Item 2</option>
</select>
EOD;
        $this->activeField->listBox(['value1' => 'Item One', 'value2' => 'Item 2'], ['options' => [
            'value1' => ['disabled' => true],
            'value2' => ['label' => 'value 2'],
        ]]);
        $this->assertEqualsWithoutLE($expectedValue, $this->activeField->parts['{input}']);

        $expectedValue = <<<'EOD'
<input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" size="4">
<option value="value1" disabled>Item One</option>
<option value="value2" selected label="value 2">Item 2</option>
</select>
EOD;
        $this->activeField->model->{$this->attributeName} = 'value2';
        $this->activeField->listBox(['value1' => 'Item One', 'value2' => 'Item 2'], ['options' => [
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
        $this->assertEmpty($actualValue);
    }

    public function testGetClientOptionsWithActiveAttributeInScenario()
    {
        $this->activeField->setClientOptionsEmpty(false);

        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');
        $this->activeField->form->enableClientValidation = false;

        // expected empty
        $actualValue = $this->activeField->getClientOptions();
        $this->assertEmpty($actualValue);
    }

    public function testGetClientOptionsClientValidation()
    {
        $this->activeField->setClientOptionsEmpty(false);

        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');
        $this->activeField->enableClientValidation = true;
        $actualValue = $this->activeField->getClientOptions();
        $expectedJsExpression = 'function (attribute, value, messages, deferred, $form) {return true;}';
        $this->assertEquals($expectedJsExpression, $actualValue['validate']);

        $this->assertNotTrue(isset($actualValue['validateOnChange']));
        $this->assertNotTrue(isset($actualValue['validateOnBlur']));
        $this->assertNotTrue(isset($actualValue['validateOnType']));
        $this->assertNotTrue(isset($actualValue['validationDelay']));
        $this->assertNotTrue(isset($actualValue['enableAjaxValidation']));

        $this->activeField->validateOnChange = $expectedValidateOnChange = false;
        $this->activeField->validateOnBlur = $expectedValidateOnBlur = false;
        $this->activeField->validateOnType = $expectedValidateOnType = true;
        $this->activeField->validationDelay = $expectedValidationDelay = 100;
        $this->activeField->enableAjaxValidation = $expectedEnableAjaxValidation = true;

        $actualValue = $this->activeField->getClientOptions();

        $this->assertSame($expectedValidateOnChange, $actualValue['validateOnChange']);
        $this->assertSame($expectedValidateOnBlur, $actualValue['validateOnBlur']);
        $this->assertSame($expectedValidateOnType, $actualValue['validateOnType']);
        $this->assertSame($expectedValidationDelay, $actualValue['validationDelay']);
        $this->assertSame($expectedEnableAjaxValidation, $actualValue['enableAjaxValidation']);
    }

    public function testGetClientOptionsValidatorWhenClientSet()
    {
        $this->activeField->setClientOptionsEmpty(false);
        $this->activeField->enableAjaxValidation = true;
        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');

        foreach ($this->activeField->model->validators as $validator) {
            $validator->whenClient = "function (attribute, value) { return 'yii2' == 'yii2'; }"; // js
        }

        $actualValue = $this->activeField->getClientOptions();
        $expectedJsExpression = 'function (attribute, value, messages, deferred, $form) {if ((function (attribute, value) '
            . "{ return 'yii2' == 'yii2'; })(attribute, value)) { return true; }}";

        $this->assertEquals($expectedJsExpression, $actualValue['validate']->expression);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/8779
     */
    public function testEnctype()
    {
        $this->activeField->fileInput();
        $this->assertEquals('multipart/form-data', $this->activeField->form->options['enctype']);
    }

    /**
     * @link https://github.com/yiisoft/yii2/issues/7627
     */
    public function testGetClientOptionsWithCustomInputId()
    {
        $this->activeField->setClientOptionsEmpty(false);

        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');
        $this->activeField->inputOptions['id'] = 'custom-input-id';
        $this->activeField->textInput();
        $actualValue = $this->activeField->getClientOptions();

        $this->assertArraySubset([
            'id' => 'activefieldtestmodel-attributename',
            'name' => $this->attributeName,
            'container' => '.field-custom-input-id',
            'input' => '#custom-input-id',
        ], $actualValue);

        $this->activeField->textInput(['id' => 'custom-textinput-id']);
        $actualValue = $this->activeField->getClientOptions();

        $this->assertArraySubset([
            'id' => 'activefieldtestmodel-attributename',
            'name' => $this->attributeName,
            'container' => '.field-custom-textinput-id',
            'input' => '#custom-textinput-id',
        ], $actualValue);
    }

    public function testAriaAttributes()
    {
        $this->activeField->addAriaAttributes = true;

        $expectedValue = <<<'EOD'
<div class="form-group field-activefieldtestmodel-attributename">
<label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
<input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">
<div class="hint-block">Hint for attributeName attribute</div>
<div class="help-block"></div>
</div>
EOD;

        $actualValue = $this->activeField->render();
        $this->assertEqualsWithoutLE($expectedValue, $actualValue);
    }

    public function testAriaRequiredAttribute()
    {
        $this->activeField->addAriaAttributes = true;
        $this->helperModel->addRule([$this->attributeName], 'required');

        $expectedValue = <<<'EOD'
<div class="form-group field-activefieldtestmodel-attributename required">
<label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
<input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" aria-required="true">
<div class="hint-block">Hint for attributeName attribute</div>
<div class="help-block"></div>
</div>
EOD;

        $actualValue = $this->activeField->render();
        $this->assertEqualsWithoutLE($expectedValue, $actualValue);
    }

    public function testAriaInvalidAttribute()
    {
        $this->activeField->addAriaAttributes = true;
        $this->helperModel->addError($this->attributeName, 'Some error');

        $expectedValue = <<<'EOD'
<div class="form-group field-activefieldtestmodel-attributename has-error">
<label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
<input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" aria-invalid="true">
<div class="hint-block">Hint for attributeName attribute</div>
<div class="help-block">Some error</div>
</div>
EOD;

        $actualValue = $this->activeField->render();
        $this->assertEqualsWithoutLE($expectedValue, $actualValue);
    }

    public function testEmptyTag()
    {
        $this->activeField->options = ['tag' => false];
        $expectedValue = '<input type="hidden" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">';
        $actualValue = $this->activeField->hiddenInput()->label(false)->error(false)->hint(false)->render();
        $this->assertEqualsWithoutLE($expectedValue, trim($actualValue));
    }

    public function testWidget()
    {
        $this->activeField->widget(TestInputWidget::className());
        $this->assertEquals('Render: ' . TestInputWidget::className(), $this->activeField->parts['{input}']);
        $widget = TestInputWidget::$lastInstance;

        $this->assertSame($this->activeField->model, $widget->model);
        $this->assertEquals($this->activeField->attribute, $widget->attribute);
        $this->assertSame($this->activeField->form->view, $widget->view);
        $this->assertSame($this->activeField, $widget->field);

        $this->activeField->widget(TestInputWidget::className(), ['options' => ['id' => 'test-id']]);
        $this->assertEquals('test-id', $this->activeField->labelOptions['for']);
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

class ActiveFieldTestModel extends DynamicModel
{
    public function attributeHints()
    {
        return [
            'attributeName' => 'Hint for attributeName attribute',
        ];
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
        return 'return true;';
    }

    public function setWhenClient($js)
    {
        $this->whenClient = $js;
    }
}

class TestInputWidget extends InputWidget
{
    /**
     * @var static
     */
    public static $lastInstance;

    public function init()
    {
        parent::init();
        self::$lastInstance = $this;
    }

    public function run()
    {
        return 'Render: ' . get_class($this);
    }
}
