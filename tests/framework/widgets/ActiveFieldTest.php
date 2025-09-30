<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\widgets;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Yii;
use yii\base\DynamicModel;
use yii\web\AssetManager;
use yii\web\View;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;
use yii\widgets\InputWidget;
use yii\widgets\MaskedInput;

/**
 * @author Nelson J Morais <njmorais@gmail.com>
 *
 * @group widgets
 */
class ActiveFieldTest extends \yiiunit\TestCase
{
    use ArraySubsetAsserts;

    private \yiiunit\framework\widgets\ActiveFieldExtend $activeField;
    /**
     * @var DynamicModel
     */
    private \yiiunit\framework\widgets\ActiveFieldTestModel $helperModel;
    /**
     * @var ActiveForm
     */
    private $helperForm;
    private string $attributeName = 'attributeName';

    protected function setUp(): void
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

    public function testRenderNoContent(): void
    {
        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[{$this->attributeName}]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
        );
    }

    /**
     * @todo discuss|review Expected HTML shouldn't be wrapped only by the $content?
     */
    public function testRenderWithCallableContent(): void
    {
        // field will be the html of the model's attribute wrapped with the return string below.
        $field = $this->attributeName;
        $content = static fn (string $field): string => "<div class=\"custom-container\"> $field </div>";

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <div class="custom-container"> <div class="form-group field-activefieldtestmodel-attributename">
            <label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[{$this->attributeName}]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div> </div>
            </div>
            HTML,
            $this->activeField->render($content),
        );
    }

    /**
     * @link https://github.com/yiisoft/yii2/issues/7627
     */
    public function testRenderWithCustomInputId(): void
    {
        $this->activeField->inputOptions['id'] = 'custom-input-id';

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-custom-input-id">
            <label class="control-label" for="custom-input-id">Attribute Name</label>
            <input type="text" id="custom-input-id" class="form-control" name="ActiveFieldTestModel[{$this->attributeName}]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
        );
    }

    public function testBeginHasErrors(): void
    {
        $this->helperModel->addError($this->attributeName, 'Error Message');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename has-error">
            HTML,
            $this->activeField->begin(),
        );
    }

    public function testBeginAttributeIsRequired(): void
    {
        $this->helperModel->addRule($this->attributeName, 'required');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename required">
            HTML,
            $this->activeField->begin(),
        );
    }

    public function testBeginHasErrorAndRequired(): void
    {
        $this->helperModel->addError($this->attributeName, 'Error Message');
        $this->helperModel->addRule($this->attributeName, 'required');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename required has-error">
            HTML,
            $this->activeField->begin(),
        );
    }

    public function testBegin(): void
    {
        $expectedValue = '<article class="form-group field-activefieldtestmodel-attributename">';
        $this->activeField->options['tag'] = 'article';
        $actualValue = $this->activeField->begin();

        $this->assertSame($expectedValue, $actualValue);

        $expectedValue = '';
        $this->activeField->options['tag'] = null;
        $actualValue = $this->activeField->begin();

        $this->assertSame($expectedValue, $actualValue);

        $expectedValue = '';
        $this->activeField->options['tag'] = false;
        $actualValue = $this->activeField->begin();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testEnd(): void
    {
        $expectedValue = '</div>';
        $actualValue = $this->activeField->end();

        $this->assertSame($expectedValue, $actualValue);

        // other tag
        $expectedValue = '</article>';
        $this->activeField->options['tag'] = 'article';
        $actualValue = $this->activeField->end();

        $this->assertSame($expectedValue, $actualValue);

        $expectedValue = '';
        $this->activeField->options['tag'] = false;
        $actualValue = $this->activeField->end();

        $this->assertSame($expectedValue, $actualValue);

        $expectedValue = '';
        $this->activeField->options['tag'] = null;
        $actualValue = $this->activeField->end();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testLabel(): void
    {
        $this->activeField->label();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );
    }

    public function testLabelInheritsLabelOptions(): void
    {
        $this->activeField->labelOptions = [
            'class' => 'inherited-class',
            'data-test' => 'inherited-data'
        ];

        $this->activeField->label(
            'Test Label',
            ['class' => 'override-class'],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label class="override-class" data-test="inherited-data" for="activefieldtestmodel-attributename">Test Label</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );
    }

    public function testLabelPriorityOfContent(): void
    {
        $label = 'Parameter Label';
        $paramLabel = 'Options Label';

        $this->activeField->label($label, ['label' => $paramLabel]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label class="control-label" for="activefieldtestmodel-attributename">{$label}</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );

        $this->activeField->label(null, ['label' => $paramLabel]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label class="control-label" for="activefieldtestmodel-attributename">{$paramLabel}</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );

        $this->activeField->label();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );
    }

    public function testLabelOptionsLabelFalseWithNullParameter(): void
    {
        $this->activeField->labelOptions = ['label' => false];

        $this->activeField->label(null);

        $this->assertEmpty(
            $this->activeField->parts['{label}'],
            'Failed asserting that label does not render.',
        );
    }

    public function testLabelOptionsLabelFalseWithNoParameters(): void
    {
        $this->activeField->labelOptions = ['label' => false];

        $this->activeField->label();

        $this->assertEmpty(
            $this->activeField->parts['{label}'],
            'Failed asserting that label does not render.',
        );
    }

    public function testLabelOverridesLabelOptionsFalse(): void
    {
        $this->activeField->labelOptions = ['label' => false];

        $this->activeField->label('Override Label');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label for="activefieldtestmodel-attributename">Override Label</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );
    }

    public function testLabelWithContent(): void
    {
        $label = 'Label Name';

        $this->activeField->label($label);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label class="control-label" for="activefieldtestmodel-attributename">{$label}</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );
    }

    public function testLabelWithEmptyString(): void
    {
        $this->activeField->label('');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label class="control-label" for="activefieldtestmodel-attributename"></label>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );
    }

    public function testLabelWithLabelOptionsAndTagCustom(): void
    {
        $label = 'Label Name';

        $this->activeField->label(
            $label,
            [
                'class' => 'custom-class',
                'tag' => 'h3',
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <h3 class="custom-class">{$label}</h3>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );

        $this->activeField->label(
            null,
            [
                'class' => 'custom-class',
                'label' => $label,
                'tag' => 'h3',
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <h3 class="custom-class">{$label}</h3>
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );
    }

    public function testLabelWithLabelOptionsAndTagFalse(): void
    {
        $label = 'Label Name';

        $this->activeField->label(
            $label,
            [
                'class' => 'custom-class',
                'tag' =>  false,
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            {$label}
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );

        $this->activeField->label(
            null,
            [
                'class' => 'custom-class',
                'label' => $label,
                'tag' => false,
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            {$label}
            HTML,
            $this->activeField->parts['{label}'],
            'Failed asserting that label renders correctly.',
        );
    }

    public function testError(): void
    {
        $this->activeField->label();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
            HTML,
           $this->activeField->parts['{label}'],
       );

        // label = false
        $expectedValue = '';
        $this->activeField->label(false);

        $this->assertSame($expectedValue, $this->activeField->parts['{label}']);

        // $label = 'Label Name'
        $label = 'Label Name';
        $this->activeField->label($label);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label class="control-label" for="activefieldtestmodel-attributename">{$label}</label>
            HTML,
            $this->activeField->parts['{label}'],
        );
    }

    public function testTabularInputErrors(): void
    {
        $this->activeField->attribute = '[0]'.$this->attributeName;
        $this->helperModel->addError($this->attributeName, 'Error Message');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-0-attributename has-error">
            HTML,
            $this->activeField->begin(),
        );
    }

    public static function hintDataProvider(): array
    {
        return [
            ['Hint Content', '<div class="hint-block">Hint Content</div>'],
            [false, ''],
            [null, '<div class="hint-block">Hint for attributeName attribute</div>'],
        ];
    }

    /**
     * @dataProvider hintDataProvider
     *
     * @param mixed $hint The hint content.
     * @param string $expectedHtml The expected HTML.
     */
    public function testHint(mixed $hint, string $expectedHtml): void
    {
        $this->activeField->hint($hint);

        $this->assertSame($expectedHtml, $this->activeField->parts['{hint}']);
    }

    public function testInput(): void
    {
        $this->activeField->input('password');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="password" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">
            HTML,
            $this->activeField->parts['{input}'],
        );

        // with options
        $this->activeField->input('password', ['weird' => 'value']);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="password" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" weird="value">
            HTML,
            $this->activeField->parts['{input}'],
        );
    }

    public function testTextInput(): void
    {
        $this->activeField->textInput();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">
            HTML,
            $this->activeField->parts['{input}'],
        );
    }

    public function testHiddenInput(): void
    {
        $this->activeField->hiddenInput();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">
            HTML,
            $this->activeField->parts['{input}'],
        );
    }

    public function testListBox(): void
    {
        $this->activeField->listBox(['1' => 'Item One', '2' => 'Item 2']);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" size="4">
            <option value="1">Item One</option>
            <option value="2">Item 2</option>
            </select>
            HTML,
            $this->activeField->parts['{input}'],
        );

        // https://github.com/yiisoft/yii2/issues/8848
        $this->activeField->listBox(['value1' => 'Item One', 'value2' => 'Item 2'], ['options' => [
            'value1' => ['disabled' => true],
            'value2' => ['label' => 'value 2'],
        ]]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" size="4">
            <option value="value1" disabled>Item One</option>
            <option value="value2" label="value 2">Item 2</option>
            </select>
            HTML,
            $this->activeField->parts['{input}'],
        );

        $this->activeField->model->{$this->attributeName} = 'value2';
        $this->activeField->listBox(['value1' => 'Item One', 'value2' => 'Item 2'], ['options' => [
            'value1' => ['disabled' => true],
            'value2' => ['label' => 'value 2'],
        ]]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" size="4">
            <option value="value1" disabled>Item One</option>
            <option value="value2" selected label="value 2">Item 2</option>
            </select>
            HTML,
            $this->activeField->parts['{input}'],
        );
    }

    public function testRadioList(): void
    {
        $this->activeField->radioList(['1' => 'Item One']);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <label class="control-label">Attribute Name</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><div id="activefieldtestmodel-attributename" role="radiogroup"><label><input type="radio" name="ActiveFieldTestModel[attributeName]" value="1"> Item One</label></div>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
        );
    }

    public function testGetClientOptionsReturnEmpty(): void
    {
        // setup: we want the real deal here!
        $this->activeField->setClientOptionsEmpty(false);

        // expected empty
        $actualValue = $this->activeField->getClientOptions();

        $this->assertEmpty($actualValue);
    }

    public function testGetClientOptionsWithActiveAttributeInScenario(): void
    {
        $this->activeField->setClientOptionsEmpty(false);

        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');
        $this->activeField->form->enableClientValidation = false;

        // expected empty
        $actualValue = $this->activeField->getClientOptions();

        $this->assertEmpty($actualValue);
    }

    public function testGetClientOptionsClientValidation(): void
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

    public function testGetClientOptionsValidatorWhenClientSet(): void
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

        $this->assertSame($expectedJsExpression, $actualValue['validate']->expression);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/8779
     */
    public function testEnctype(): void
    {
        $this->activeField->fileInput();

        $this->assertSame('multipart/form-data', $this->activeField->form->options['enctype']);
    }

    /**
     * @link https://github.com/yiisoft/yii2/issues/7627
     */
    public function testGetClientOptionsWithCustomInputId(): void
    {
        $this->activeField->setClientOptionsEmpty(false);

        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');
        $this->activeField->inputOptions['id'] = 'custom-input-id';
        $this->activeField->textInput();
        $actualValue = $this->activeField->getClientOptions();

        $this->assertArraySubset([
            'id' => 'custom-input-id',
            'name' => $this->attributeName,
            'container' => '.field-custom-input-id',
            'input' => '#custom-input-id',
        ], $actualValue);

        $this->activeField->textInput(['id' => 'custom-textinput-id']);
        $actualValue = $this->activeField->getClientOptions();

        $this->assertArraySubset([
            'id' => 'custom-textinput-id',
            'name' => $this->attributeName,
            'container' => '.field-custom-textinput-id',
            'input' => '#custom-textinput-id',
        ], $actualValue);
    }

    public function testAriaAttributes(): void
    {
        $this->activeField->addAriaAttributes = true;

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
        );
    }

    public function testAriaRequiredAttribute(): void
    {
        $this->activeField->addAriaAttributes = true;
        $this->helperModel->addRule([$this->attributeName], 'required');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename required">
            <label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" aria-required="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
        );
    }

    public function testAriaInvalidAttribute(): void
    {
        $this->activeField->addAriaAttributes = true;
        $this->helperModel->addError($this->attributeName, 'Some error');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename has-error">
            <label class="control-label" for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]" aria-invalid="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block">Some error</div>
            </div>
            HTML,
            $this->activeField->render(),
        );
    }

    public function testTabularAriaAttributes(): void
    {
        $this->activeField->attribute = '[0]' . $this->attributeName;
        $this->activeField->addAriaAttributes = true;

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-0-attributename">
            <label class="control-label" for="activefieldtestmodel-0-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-0-attributename" class="form-control" name="ActiveFieldTestModel[0][attributeName]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
        );
    }

    public function testTabularAriaRequiredAttribute(): void
    {
        $this->activeField->attribute = '[0]' . $this->attributeName;
        $this->activeField->addAriaAttributes = true;
        $this->helperModel->addRule([$this->attributeName], 'required');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-0-attributename required">
            <label class="control-label" for="activefieldtestmodel-0-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-0-attributename" class="form-control" name="ActiveFieldTestModel[0][attributeName]" aria-required="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
        );
    }

    public function testTabularAriaInvalidAttribute(): void
    {
        $this->activeField->attribute = '[0]' . $this->attributeName;
        $this->activeField->addAriaAttributes = true;
        $this->helperModel->addError($this->attributeName, 'Some error');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-0-attributename has-error">
            <label class="control-label" for="activefieldtestmodel-0-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-0-attributename" class="form-control" name="ActiveFieldTestModel[0][attributeName]" aria-invalid="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block">Some error</div>
            </div>
            HTML,
            $this->activeField->render(),
        );
    }

    public function testEmptyTag(): void
    {
        $this->activeField->options = ['tag' => false];

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">
            HTML,
            trim($this->activeField->hiddenInput()->label(false)->error(false)->hint(false)->render()),
        );
    }

    public function testWidget(): void
    {
        $this->activeField->widget(TestInputWidget::class);
        $this->assertSame('Render: ' . TestInputWidget::class, $this->activeField->parts['{input}']);
        $widget = TestInputWidget::$lastInstance;

        $this->assertSame($this->activeField->model, $widget->model);
        $this->assertSame($this->activeField->attribute, $widget->attribute);
        $this->assertSame($this->activeField->form->view, $widget->view);
        $this->assertSame($this->activeField, $widget->field);

        $this->activeField->widget(TestInputWidget::class, ['options' => ['id' => 'test-id']]);
        $this->assertSame('test-id', $this->activeField->labelOptions['for']);
    }

    public function testWidgetOptions(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;
        $this->activeField->model->addError('attributeName', 'error');

        $this->activeField->widget(TestInputWidget::class);
        $widget = TestInputWidget::$lastInstance;
        $expectedOptions = [
            'class' => 'form-control has-error',
            'aria-invalid' => 'true',
            'id' => 'activefieldtestmodel-attributename',
        ];
        $this->assertSame($expectedOptions, $widget->options);

        $this->activeField->inputOptions = [];
        $this->activeField->widget(TestInputWidget::class);
        $widget = TestInputWidget::$lastInstance;
        $expectedOptions = [
            'class' => 'has-error',
            'aria-invalid' => 'true',
            'id' => 'activefieldtestmodel-attributename',
        ];
        $this->assertSame($expectedOptions, $widget->options);
    }

    /**
     * @depends testHiddenInput
     *
     * @see https://github.com/yiisoft/yii2/issues/14773
     */
    public function testOptionsClass(): void
    {
        $this->activeField->options = ['class' => 'test-wrapper'];

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="test-wrapper field-activefieldtestmodel-attributename">

            <input type="hidden" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">


            </div>
            HTML,
            trim($this->activeField->hiddenInput()->label(false)->error(false)->hint(false)->render()),
        );

        $this->activeField->options = ['class' => ['test-wrapper', 'test-add']];

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="test-wrapper test-add field-activefieldtestmodel-attributename">

            <input type="hidden" id="activefieldtestmodel-attributename" class="form-control" name="ActiveFieldTestModel[attributeName]">


            </div>
            HTML,
            trim($this->activeField->hiddenInput()->label(false)->error(false)->hint(false)->render()),
        );
    }

    public function testInputOptionsTransferToWidget(): void
    {
        $widget = $this->activeField->widget(TestMaskedInput::class, [
            'mask' => '999-999-9999',
            'options' => ['placeholder' => 'pholder_direct'],
        ]);
        $this->assertStringContainsString('placeholder="pholder_direct"', (string) $widget);

        // use regex clientOptions instead mask
        $widget = $this->activeField->widget(TestMaskedInput::class, [
            'options' => ['placeholder' => 'pholder_direct'],
            'clientOptions' => ['regex' => '^.*$'],
        ]);
        $this->assertStringContainsString('placeholder="pholder_direct"', (string) $widget);

        // transfer options from ActiveField to widget
        $this->activeField->inputOptions = ['placeholder' => 'pholder_input'];
        $widget = $this->activeField->widget(TestMaskedInput::class, [
            'mask' => '999-999-9999',
        ]);
        $this->assertStringContainsString('placeholder="pholder_input"', (string) $widget);

        // set both AF and widget options (second one takes precedence)
        $this->activeField->inputOptions = ['placeholder' => 'pholder_both_input'];
        $widget = $this->activeField->widget(TestMaskedInput::class, [
            'mask' => '999-999-9999',
            'options' => ['placeholder' => 'pholder_both_direct']
        ]);
        $this->assertStringContainsString('placeholder="pholder_both_direct"', (string) $widget);
    }

    public function testRadioEnclosedByLabelFalseWithLabelOptions(): void
    {
        $this->activeField->radio(
            [
                'label' => 'Select Option A',
                'labelOptions' => [
                    'class' => 'custom-radio-label',
                    'data-option' => 'option-a',
                ],
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <label class="custom-radio-label" data-option="option-a" for="activefieldtestmodel-attributename">Select Option A</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="radio" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that radio renders correctly.',
        );
    }

    public function testRadioEnclosedByLabelFalseWithLabelOptionsAndLabelFalse(): void
    {
        $this->activeField->radio(
            [
                'label' => false,
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">

            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="radio" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that radio renders correctly.',
        );
    }

    public function testRadioEnclosedByLabelFalseWithLabelOptionsAndTagLabel(): void
    {
        $this->activeField->radio(
            [
                'label' => 'Choose This Option',
                'labelOptions' => [
                    'class' => 'radio-option-label',
                    'data-value' => 'choice-1',
                    'tag' => 'span',
                ],
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <span class="radio-option-label" data-value="choice-1">Choose This Option</span>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="radio" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that radio renders correctly.',
        );
    }

    public function testRadioEnclosedByLabelFalseWithLabelOptionsAndTagLabelFalse(): void
    {
        $this->activeField->radio(
            [
                'label' => '<div class="custom-label-wrapper"><strong>Premium Option</strong> <em>(Recommended)</em></div>',
                'labelOptions' => [
                    'tag' => false,
                ],
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <div class="custom-label-wrapper"><strong>Premium Option</strong> <em>(Recommended)</em></div>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="radio" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that radio renders correctly.',
        );
    }

    public function testRadioEnclosedByLabelFalseWithoutLabelOptions(): void
    {
        $this->activeField->radio(
            [
                'label' => 'Select Option A',
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            Select Option A
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="radio" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that radio renders correctly.',
        );
    }

    public function testCheckboxEnclosedByLabelFalseWithLabelOptions(): void
    {
        $this->activeField->checkbox(
            [
                'label' => 'Custom Label',
                'labelOptions' => [
                    'class' => 'custom-label-class',
                    'data-test' => 'custom-label-data',
                ],
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <label class="custom-label-class" data-test="custom-label-data" for="activefieldtestmodel-attributename">Custom Label</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="checkbox" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that checkbox renders correctly.',
        );
    }

    public function testCheckboxEnclosedByLabelFalseWithLabelOptionsAndLabelFalse(): void
    {
        $this->activeField->checkbox(
            [
                'label' => false,
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">

            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="checkbox" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that checkbox renders correctly.',
        );
    }

    public function testCheckboxEnclosedByLabelFalseWithLabelOptionsAndTagLabel(): void
    {
        $this->activeField->checkbox(
            [
                'label' => 'Custom Label',
                'labelOptions' => [
                    'class' => 'custom-label-class',
                    'data-test' => 'custom-label-data',
                    'tag' => 'span',
                ],
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <span class="custom-label-class" data-test="custom-label-data">Custom Label</span>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="checkbox" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that checkbox renders correctly.',
        );
    }

    public function testCheckboxEnclosedByLabelFalseWithLabelOptionsAndTagLabelFalse(): void
    {
        $this->activeField->checkbox(
            [
                'label' => '<div class="custom-label-wrapper"><strong>Custom</strong> <em>Label</em></div>',
                'labelOptions' => [
                    'tag' => false,
                ],
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            <div class="custom-label-wrapper"><strong>Custom</strong> <em>Label</em></div>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="checkbox" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that checkbox renders correctly.',
        );
    }

    public function testCheckboxEnclosedByLabelFalseWithoutLabelOptions(): void
    {
        $this->activeField->checkbox(
            [
                'label' => 'Custom Label',
            ],
            false,
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-activefieldtestmodel-attributename">
            Custom Label
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><input type="checkbox" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="help-block"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Failed asserting that checkbox renders correctly.',
        );
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
 * Helper Classes.
 */
class ActiveFieldExtend extends ActiveField
{
    public function __construct(private $getClientOptionsEmpty = true)
    {
    }

    public function setClientOptionsEmpty($value): void
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

    public function setWhenClient($js): void
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

    public function init(): void
    {
        parent::init();
        self::$lastInstance = $this;
    }

    public function run()
    {
        return 'Render: ' . static::class;
    }
}

class TestMaskedInput extends MaskedInput
{
    /**
     * @var static
     */
    public static $lastInstance;

    public function init(): void
    {
        parent::init();
        self::$lastInstance = $this;
    }

    public function getOptions() {
        return $this->options;
    }

    public function run()
    {
        return 'Options: ' . implode(', ', array_map(
            fn($v, $k) => sprintf('%s="%s"', $k, $v),
            $this->options,
            array_keys($this->options)
        ));
    }
}

class TestActiveFieldWithException extends ActiveField
{
    public function render($content = null)
    {
        throw new \Exception('Test exception in toString.');
    }
}

