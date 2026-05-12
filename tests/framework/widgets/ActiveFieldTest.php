<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use Exception;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\DynamicModel;
use yii\validators\Validator;
use yii\web\AssetManager;
use yii\web\View;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;
use yii\widgets\InputWidget;
use yii\widgets\MaskedInput;
use yiiunit\framework\widgets\providers\ActiveFieldProvider;
use yiiunit\TestCase;

use function array_keys;
use function array_map;
use function sprintf;

/**
 * Unit tests for {@see ActiveField} widget.
 *
 * @author Nelson J Morais <njmorais@gmail.com>
 */
#[Group('widgets')]
#[Group('active-field')]
class ActiveFieldTest extends TestCase
{
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
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[{$this->attributeName}]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    /**
     * @todo discuss|review Expected HTML shouldn't be wrapped only by the $content?
     */
    public function testRenderWithCallableContent(): void
    {
        // field will be the html of the model's attribute wrapped with the return string below.
        $field = $this->attributeName;
        $content = static fn (string $field): string => "<div class=\"custom-container\">\n$field\n</div>";

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <div class="custom-container">
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[{$this->attributeName}]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            </div>
            </div>
            HTML,
            $this->activeField->render($content),
            'Rendered HTML does not match expected output',
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
            <div class="field-custom-input-id">
            <label for="custom-input-id">Attribute Name</label>
            <input type="text" id="custom-input-id" name="ActiveFieldTestModel[{$this->attributeName}]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testBeginHasErrors(): void
    {
        $this->helperModel->addError($this->attributeName, 'Error Message');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            HTML,
            $this->activeField->begin(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testBeginAttributeIsRequired(): void
    {
        $this->helperModel->addRule($this->attributeName, 'required');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename required">
            HTML,
            $this->activeField->begin(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testBeginHasErrorAndRequired(): void
    {
        $this->helperModel->addError($this->attributeName, 'Error Message');
        $this->helperModel->addRule($this->attributeName, 'required');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename required">
            HTML,
            $this->activeField->begin(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testBegin(): void
    {
        $this->activeField->options['tag'] = 'article';

        $this->assertSame(
            <<<HTML
            <article class="field-activefieldtestmodel-attributename">
            HTML,
            $this->activeField->begin(),
            'Rendered HTML does not match expected output',
        );

        $this->activeField->options['tag'] = null;

        $this->assertEmpty(
            $this->activeField->begin(),
            "Failed asserting that 'begin()' does not render.",
        );

        $this->activeField->options['tag'] = false;

        $this->assertEmpty(
            $this->activeField->begin(),
            "Failed asserting that 'begin()' does not render.",
        );
    }

    public function testEnd(): void
    {
        $this->assertSame(
            <<<HTML
            </div>
            HTML,
            $this->activeField->end(),
            'Rendered HTML does not match expected output',
        );

        // other tag
        $this->activeField->options['tag'] = 'article';

        $this->assertSame(
            <<<HTML
            </article>
            HTML,
            $this->activeField->end(),
            'Rendered HTML does not match expected output',
        );

        $this->activeField->options['tag'] = false;

        $this->assertEmpty(
            $this->activeField->end(),
            "Failed asserting that 'end()' does not render.",
        );

        $this->activeField->options['tag'] = null;

        $this->assertEmpty(
            $this->activeField->end(),
            "Failed asserting that 'end()' does not render.",
        );
    }

    public function testLabel(): void
    {
        $this->activeField->label();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Rendered HTML does not match expected output',
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
            'Rendered HTML does not match expected output',
        );
    }

    public function testLabelPriorityOfContent(): void
    {
        $label = 'Parameter Label';
        $paramLabel = 'Options Label';

        $this->activeField->label($label, ['label' => $paramLabel]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label for="activefieldtestmodel-attributename">{$label}</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Rendered HTML does not match expected output',
        );

        $this->activeField->label(null, ['label' => $paramLabel]);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label for="activefieldtestmodel-attributename">{$paramLabel}</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Rendered HTML does not match expected output',
        );

        $this->activeField->label();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Rendered HTML does not match expected output',
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
            'Rendered HTML does not match expected output',
        );
    }

    public function testLabelWithContent(): void
    {
        $label = 'Label Name';

        $this->activeField->label($label);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label for="activefieldtestmodel-attributename">{$label}</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Rendered HTML does not match expected output',
        );
    }

    public function testLabelWithEmptyString(): void
    {
        $this->activeField->label('');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label for="activefieldtestmodel-attributename"></label>
            HTML,
            $this->activeField->parts['{label}'],
            'Rendered HTML does not match expected output',
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
            'Rendered HTML does not match expected output',
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
            'Rendered HTML does not match expected output',
        );
    }

    public function testLabelWithLabelOptionsAndTagFalse(): void
    {
        $label = 'Label Name';

        $this->activeField->label(
            $label,
            [
                'class' => 'custom-class',
                'tag' => false,
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            {$label}
            HTML,
            $this->activeField->parts['{label}'],
            'Rendered HTML does not match expected output',
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
            'Rendered HTML does not match expected output',
        );
    }

    public function testError(): void
    {
        $this->activeField->label();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Rendered HTML does not match expected output',
        );

        // label = false
        $this->activeField->label(false);

        $this->assertEmpty(
            $this->activeField->parts['{label}'],
            'Failed asserting that label does not render.',
        );

        // $label = 'Label Name'
        $label = 'Label Name';
        $this->activeField->label($label);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <label for="activefieldtestmodel-attributename">{$label}</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Rendered HTML does not match expected output',
        );
    }

    public function testTabularInputErrors(): void
    {
        $this->activeField->attribute = "[0]$this->attributeName";

        $this->helperModel->addError($this->attributeName, 'Error Message');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-0-attributename">
            HTML,
            $this->activeField->begin(),
            'Rendered HTML does not match expected output',
        );
    }

    #[DataProviderExternal(ActiveFieldProvider::class, 'hint')]
    public function testHint(bool|string|null $hint, string $expectedHtml): void
    {
        $this->activeField->hint($hint);

        $this->assertSame(
            $expectedHtml,
            $this->activeField->parts['{hint}'],
            'Rendered HTML does not match expected output',
        );
    }

    public function testInput(): void
    {
        $this->activeField->input('password');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="password" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]">
            HTML,
            $this->activeField->parts['{input}'],
            'Rendered HTML does not match expected output',
        );

        // with options
        $this->activeField->input('password', ['weird' => 'value']);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="password" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" weird="value">
            HTML,
            $this->activeField->parts['{input}'],
            'Rendered HTML does not match expected output',
        );
    }

    public function testTextInput(): void
    {
        $this->activeField->textInput();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="text" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]">
            HTML,
            $this->activeField->parts['{input}'],
            'Rendered HTML does not match expected output',
        );
    }

    public function testHiddenInput(): void
    {
        $this->activeField->hiddenInput();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]">
            HTML,
            $this->activeField->parts['{input}'],
            'Rendered HTML does not match expected output',
        );
    }

    public function testListBox(): void
    {
        $this->activeField->listBox(
            [
                '1' => 'Item One',
                '2' => 'Item 2',
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" size="4">
            <option value="1">Item One</option>
            <option value="2">Item 2</option>
            </select>
            HTML,
            $this->activeField->parts['{input}'],
            'Rendered HTML does not match expected output',
        );

        // https://github.com/yiisoft/yii2/issues/8848
        $this->activeField->listBox(
            [
                'value1' => 'Item One',
                'value2' => 'Item 2',
            ],
            [
                'options' => [
                    'value1' => ['disabled' => true],
                    'value2' => ['label' => 'value 2'],
                ],
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" size="4">
            <option value="value1" disabled>Item One</option>
            <option value="value2" label="value 2">Item 2</option>
            </select>
            HTML,
            $this->activeField->parts['{input}'],
            'Rendered HTML does not match expected output',
        );

        $this->activeField->model->{$this->attributeName} = 'value2';

        $this->activeField->listBox(
            [
                'value1' => 'Item One',
                'value2' => 'Item 2',
            ],
            [
                'options' => [
                    'value1' => ['disabled' => true],
                    'value2' => ['label' => 'value 2'],
                ],
            ]
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" size="4">
            <option value="value1" disabled>Item One</option>
            <option value="value2" selected label="value 2">Item 2</option>
            </select>
            HTML,
            $this->activeField->parts['{input}'],
            'Rendered HTML does not match expected output',
        );
    }

    public function testRadioList(): void
    {
        $this->activeField->radioList(['1' => 'Item One']);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label>Attribute Name</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><div id="activefieldtestmodel-attributename" role="radiogroup"><label><input type="radio" name="ActiveFieldTestModel[attributeName]" value="1"> Item One</label></div>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testGetClientOptionsReturnEmpty(): void
    {
        // setup: we want the real deal here!
        $this->activeField->setClientOptionsEmpty(false);

        $this->assertEmpty(
            $this->activeField->getClientOptions(),
            "'getClientOptions()' method should return an empty array.",
        );
    }

    public function testGetClientOptionsWithActiveAttributeInScenario(): void
    {
        $this->activeField->setClientOptionsEmpty(false);
        $this->activeField->model->addRule($this->attributeName, 'yiiunit\framework\widgets\TestValidator');

        $this->activeField->form->enableClientValidation = false;

        $this->assertEmpty(
            $this->activeField->getClientOptions(),
            "'getClientOptions()' method should return an empty array.",
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/8779
     */
    public function testEnctype(): void
    {
        $this->activeField->fileInput();

        $this->assertSame(
            'multipart/form-data',
            $this->activeField->form->options['enctype'],
            'Should be the same as the set value.',
        );
    }


    public function testAriaAttributes(): void
    {
        $this->activeField->addAriaAttributes = true;

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testAriaRequiredAttribute(): void
    {
        $this->activeField->addAriaAttributes = true;

        $this->helperModel->addRule([$this->attributeName], 'required');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename required">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" aria-required="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testAriaInvalidAttribute(): void
    {
        $this->activeField->addAriaAttributes = true;

        $this->helperModel->addError($this->attributeName, 'Some error');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" aria-invalid="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">Some error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testTabularAriaAttributes(): void
    {
        $this->activeField->attribute = "[0]{$this->attributeName}";
        $this->activeField->addAriaAttributes = true;

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-0-attributename">
            <label for="activefieldtestmodel-0-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-0-attributename" name="ActiveFieldTestModel[0][attributeName]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testTabularAriaRequiredAttribute(): void
    {
        $this->activeField->attribute = "[0]{$this->attributeName}";
        $this->activeField->addAriaAttributes = true;

        $this->helperModel->addRule([$this->attributeName], 'required');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-0-attributename required">
            <label for="activefieldtestmodel-0-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-0-attributename" name="ActiveFieldTestModel[0][attributeName]" aria-required="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testTabularAriaInvalidAttribute(): void
    {
        $this->activeField->attribute = "[0]{$this->attributeName}";
        $this->activeField->addAriaAttributes = true;

        $this->helperModel->addError($this->attributeName, 'Some error');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-0-attributename">
            <label for="activefieldtestmodel-0-attributename">Attribute Name</label>
            <input type="text" id="activefieldtestmodel-0-attributename" name="ActiveFieldTestModel[0][attributeName]" aria-invalid="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">Some error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testEmptyTag(): void
    {
        $this->activeField->options = ['tag' => false];

        $this->assertEqualsWithoutLE(
            <<<HTML
            <input type="hidden" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]">
            HTML,
            trim($this->activeField->hiddenInput()->label(false)->error(false)->hint(false)->render()),
            'Rendered HTML does not match expected output',
        );
    }

    public function testWidget(): void
    {
        $this->activeField->widget(TestInputWidget::class);

        $this->assertSame(
            'Render: ' . TestInputWidget::class,
            $this->activeField->parts['{input}'],
            'Rendered HTML does not match expected output',
        );

        $widget = TestInputWidget::$lastInstance;

        $this->assertSame(
            $this->activeField->model,
            $widget->model,
            'Should be the same as the set value.',
        );
        $this->assertSame(
            $this->activeField->attribute,
            $widget->attribute,
            'Should be the same as the set value.',
        );
        $this->assertSame(
            $this->activeField->form->view,
            $widget->view,
            'Should be the same as the set value.',
        );
        $this->assertSame(
            $this->activeField,
            $widget->field,
            'Should be the same as the set value.',
        );

        $this->activeField->widget(TestInputWidget::class, ['options' => ['id' => 'test-id']]);

        $this->assertSame(
            'test-id',
            $this->activeField->labelOptions['for'],
            'Should be the same as the set value.',
        );
    }

    public function testWidgetOptions(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;

        $this->activeField->model->addError('attributeName', 'error');

        $this->activeField->widget(TestInputWidget::class);
        $widget = TestInputWidget::$lastInstance;

        $expectedOptions = [
            'aria-invalid' => 'true',
            'id' => 'activefieldtestmodel-attributename',
        ];

        $this->assertSame(
            $expectedOptions,
            $widget->options,
            'Should be the same as the set value.',
        );

        $this->activeField->inputOptions = [];

        $this->activeField->widget(TestInputWidget::class);

        $widget = TestInputWidget::$lastInstance;

        $expectedOptions = [
            'aria-invalid' => 'true',
            'id' => 'activefieldtestmodel-attributename',
        ];

        $this->assertSame(
            $expectedOptions,
            $widget->options,
            'Should be the same as the set value.',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14773
     */
    #[Depends('testHiddenInput')]
    public function testOptionsClass(): void
    {
        $this->activeField->options = ['class' => 'test-wrapper'];

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="test-wrapper field-activefieldtestmodel-attributename">
            <input type="hidden" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]">
            </div>
            HTML,
            $this->normalizeHTML($this->activeField->hiddenInput()->label(false)->error(false)->hint(false)->render()),
            'Rendered HTML does not match expected output',
        );

        $this->activeField->options = ['class' => ['test-wrapper', 'test-add']];

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="test-wrapper test-add field-activefieldtestmodel-attributename">
            <input type="hidden" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]">
            </div>
            HTML,
            $this->normalizeHTML($this->activeField->hiddenInput()->label(false)->error(false)->hint(false)->render()),
            'Rendered HTML does not match expected output',
        );
    }

    public function testInputOptionsTransferToWidget(): void
    {
        $widget = $this->activeField->widget(
            TestMaskedInput::class,
            [
                'mask' => '999-999-9999',
                'options' => ['placeholder' => 'pholder_direct'],
            ],
        );

        $this->assertStringContainsString(
            'placeholder="pholder_direct"',
            (string) $widget,
            'Should be the same as the set value.',
        );

        // use regex clientOptions instead mask
        $widget = $this->activeField->widget(
            TestMaskedInput::class,
            [
                'options' => ['placeholder' => 'pholder_direct'],
                'clientOptions' => ['regex' => '^.*$'],
            ],
        );

        $this->assertStringContainsString(
            'placeholder="pholder_direct"',
            (string) $widget,
            'Should be the same as the set value.',
        );

        // transfer options from ActiveField to widget
        $this->activeField->inputOptions = ['placeholder' => 'pholder_input'];

        $widget = $this->activeField->widget(
            TestMaskedInput::class,
            ['mask' => '999-999-9999'],
        );

        $this->assertStringContainsString(
            'placeholder="pholder_input"',
            (string) $widget,
            'Should be the same as the set value.',
        );

        // set both AF and widget options (second one takes precedence)
        $this->activeField->inputOptions = ['placeholder' => 'pholder_both_input'];

        $widget = $this->activeField->widget(
            TestMaskedInput::class,
            [
                'mask' => '999-999-9999',
                'options' => ['placeholder' => 'pholder_both_direct']
            ],
        );

        $this->assertStringContainsString(
            'placeholder="pholder_both_direct"',
            (string) $widget,
            'Should be the same as the set value.',
        );
    }

    #[DataProviderExternal(ActiveFieldProvider::class, 'radioEnclosedByLabelFalse')]
    public function testRadioEnclosedByLabelFalse(array $options, string $expectedLabel): void
    {
        $this->activeField->radio($options, false);

        self::assertSame(
            $expectedLabel,
            $this->activeField->parts['{label}'],
            "Should render the expected label when 'enclosedByLabel' is 'false'.",
        );
    }

    #[DataProviderExternal(ActiveFieldProvider::class, 'checkboxEnclosedByLabelFalse')]
    public function testCheckboxEnclosedByLabelFalse(array $options, string $expectedLabel): void
    {
        $this->activeField->checkbox($options, false);

        self::assertSame(
            $expectedLabel,
            $this->activeField->parts['{label}'],
            "Should render the expected label when 'enclosedByLabel' is 'false'.",
        );
    }

    public function testRadioEnclosedByLabelFalsePreservesExistingLabel(): void
    {
        $this->activeField->label('Existing Label');
        $this->activeField->radio(['label' => 'Radio Label'], false);

        self::assertSame(
            <<<HTML
            <label for="activefieldtestmodel-attributename">Existing Label</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Should preserve the label set by a prior label call.',
        );
    }

    public function testCheckboxEnclosedByLabelFalsePreservesExistingLabel(): void
    {
        $this->activeField->label('Existing Label');
        $this->activeField->checkbox(['label' => 'Checkbox Label'], false);

        self::assertSame(
            <<<HTML
            <label for="activefieldtestmodel-attributename">Existing Label</label>
            HTML,
            $this->activeField->parts['{label}'],
            'Should preserve the label set by a prior label call.',
        );
    }

    public function testRadioEnclosedByLabelFalsePreservesLabelFalse(): void
    {
        $this->activeField->label(false);
        $this->activeField->radio(['label' => 'Radio Label'], false);

        self::assertSame(
            '',
            $this->activeField->parts['{label}'],
            "Should preserve label 'false' and not regenerate a label.",
        );
    }

    public function testCheckboxEnclosedByLabelFalsePreservesLabelFalse(): void
    {
        $this->activeField->label(false);
        $this->activeField->checkbox(['label' => 'Checkbox Label'], false);

        self::assertSame(
            '',
            $this->activeField->parts['{label}'],
            "Should preserve label 'false' and not regenerate a label.",
        );
    }

    public function testRadioEnclosedByLabelFalseWithEmptyLabelOptions(): void
    {
        $this->activeField->radio(
            [
                'label' => 'Radio Label',
                'labelOptions' => [],
            ],
            false,
        );

        self::assertSame(
            <<<HTML
            <label for="activefieldtestmodel-attributename">Radio Label</label>
            HTML,
            $this->activeField->parts['{label}'],
            "Empty 'labelOptions' should default to a wrapped label tag.",
        );
    }

    public function testCheckboxEnclosedByLabelFalseWithEmptyLabelOptions(): void
    {
        $this->activeField->checkbox(
            [
                'label' => 'Checkbox Label',
                'labelOptions' => [],
            ],
            false,
        );

        self::assertSame(
            <<<HTML
            <label for="activefieldtestmodel-attributename">Checkbox Label</label>
            HTML,
            $this->activeField->parts['{label}'],
            "Empty 'labelOptions' should default to a wrapped label tag.",
        );
    }

    public function testGenerateLabelDoesNotMutateLabelOptions(): void
    {
        $originalLabelOptions = $this->activeField->labelOptions;

        $this->activeField->radio(
            [
                'label' => 'Radio Label',
                'labelOptions' => [
                    'class' => 'temporary-class',
                    'data-temp' => 'value',
                ],
            ],
            false,
        );

        self::assertSame(
            $originalLabelOptions,
            $this->activeField->labelOptions,
            "Should not mutate 'labelOptions' property when generating the label.",
        );
    }

    public function testRadioExplicitLabelOverridesLabelOptionsLabel(): void
    {
        $this->activeField->labelOptions = ['label' => false];

        $this->activeField->radio(
            [
                'label' => 'Explicit Radio Label',
                'labelOptions' => ['class' => 'custom'],
            ],
            false,
        );

        self::assertSame(
            <<<HTML
            <label class="custom" for="activefieldtestmodel-attributename">Explicit Radio Label</label>
            HTML,
            $this->activeField->parts['{label}'],
            "Explicit radio label should override 'labelOptions' when| label value is 'false'.",
        );
    }

    public function testInputWithValidationStateOnInput(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;

        $this->activeField->model->addError($this->attributeName, 'Input validation error');
        $this->activeField->input('number');

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="number" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" aria-invalid="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">Input validation error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testPasswordInput(): void
    {
        $this->activeField->passwordInput();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="password" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testPasswordInputWithValidationStateOnInput(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;
        $this->activeField->model->addError($this->attributeName, 'Password error');

        $this->activeField->passwordInput();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="password" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" aria-invalid="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">Password error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testFileInputWithCustomInputOptions(): void
    {
        $this->activeField->inputOptions = ['class' => 'custom-file-input', 'data-test' => 'file-upload'];

        $this->activeField->fileInput(['accept' => 'image/*', 'id' => 'custom-file-id']);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-custom-file-id">
            <label for="custom-file-id">Attribute Name</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><input type="file" id="custom-file-id" class="custom-file-input" name="ActiveFieldTestModel[attributeName]" data-test="file-upload" accept="image/*">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testFileInputWithValidationStateOnInput(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;

        $this->activeField->model->addError($this->attributeName, 'File upload error');

        $this->activeField->fileInput();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><input type="file" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" aria-invalid="true">
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">File upload error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testTextarea(): void
    {
        $this->activeField->textarea();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <textarea id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]"></textarea>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testTextareaWithValidationStateOnInput(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;
        $this->activeField->model->addError($this->attributeName, 'Some error');

        $this->activeField->textarea();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <textarea id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" aria-invalid="true"></textarea>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">Some error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testRadioEnclosedByLabelTrue(): void
    {
        $this->activeField->radio([], true);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><label><input type="radio" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1"> Attribute Name</label>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->normalizeHTML($this->activeField->render()),
            'Rendered HTML does not match expected output',
        );
    }

    public function testCheckboxEnclosedByLabelTrue(): void
    {
        $this->activeField->checkbox([], true);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value="0"><label><input type="checkbox" id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" value="1"> Attribute Name</label>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->normalizeHTML($this->activeField->render()),
            'Rendered HTML does not match expected output',
        );
    }

    public function testDropDownList(): void
    {
        $this->activeField->dropDownList(
            [
                '1' => 'Item One',
                '2' => 'Item Two',
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <select id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]">
            <option value="1">Item One</option>
            <option value="2">Item Two</option>
            </select>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testDropDownListWithValidationStateOnInput(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;

        $this->activeField->model->addError($this->attributeName, 'Some error');
        $this->activeField->dropDownList(['1' => 'Item One']);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <select id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" aria-invalid="true">
            <option value="1">Item One</option>
            </select>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">Some error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testListboxWithValidationStateOnInput(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;

        $this->activeField->model->addError($this->attributeName, 'Some error');
        $this->activeField->listBox(
            [
                '1' => 'Item One',
                '2' => 'Item 2',
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label for="activefieldtestmodel-attributename">Attribute Name</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><select id="activefieldtestmodel-attributename" name="ActiveFieldTestModel[attributeName]" size="4" aria-invalid="true">
            <option value="1">Item One</option>
            <option value="2">Item 2</option>
            </select>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">Some error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testCheckboxList(): void
    {
        $this->activeField->checkboxList(
            [
                '1' => 'Item One',
                '2' => 'Item Two',
            ],
        );

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label>Attribute Name</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><div id="activefieldtestmodel-attributename"><label><input type="checkbox" name="ActiveFieldTestModel[attributeName][]" value="1"> Item One</label>
            <label><input type="checkbox" name="ActiveFieldTestModel[attributeName][]" value="2"> Item Two</label></div>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error"></div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testCheckboxListWithValidationStateOnInput(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;

        $this->activeField->model->addError($this->attributeName, 'Some error');
        $this->activeField->checkboxList(['1' => 'Item One']);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label>Attribute Name</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><div id="activefieldtestmodel-attributename" aria-invalid="true"><label><input type="checkbox" name="ActiveFieldTestModel[attributeName][]" value="1"> Item One</label></div>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">Some error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    public function testRadioListWithValidationStateOnInput(): void
    {
        $this->activeField->form->validationStateOn = ActiveForm::VALIDATION_STATE_ON_INPUT;

        $this->activeField->model->addError($this->attributeName, 'Some error');
        $this->activeField->radioList(['1' => 'Item One']);

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="field-activefieldtestmodel-attributename">
            <label>Attribute Name</label>
            <input type="hidden" name="ActiveFieldTestModel[attributeName]" value=""><div id="activefieldtestmodel-attributename" role="radiogroup" aria-invalid="true"><label><input type="radio" name="ActiveFieldTestModel[attributeName]" value="1"> Item One</label></div>
            <div class="hint-block">Hint for attributeName attribute</div>
            <div class="field-error">Some error</div>
            </div>
            HTML,
            $this->activeField->render(),
            'Rendered HTML does not match expected output',
        );
    }

    /**
     * Helper methods.
     */
    protected function getView()
    {
        $view = new View();

        $view->setAssetManager(
            new AssetManager(
                [
                    'basePath' => '@testWebRoot/assets',
                    'baseUrl' => '@testWeb/assets',
                ],
            ),
        );

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
 *
 * @property ActiveFieldTestModel $model
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

class TestValidator extends Validator
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

    public function getOptions()
    {
        return $this->options;
    }

    public function run()
    {
        return 'Options: ' . implode(
            ', ',
            array_map(
                static fn ($v, $k): string => sprintf('%s="%s"', $k, $v),
                $this->options,
                array_keys($this->options),
            ),
        );
    }
}

class TestActiveFieldWithException extends ActiveField
{
    public function render($content = null)
    {
        throw new Exception('Test exception in toString.');
    }
}
