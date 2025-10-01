<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\widgets;

use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidCallException;
use yii\widgets\ActiveForm;

/**
 * @group widgets
 */
final class ActiveFormTest extends \yiiunit\TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_URI'] = 'https://example.com/';

        parent::setUp();

        $this->mockWebApplication();

        Yii::$app->assetManager->hashCallback = static fn ($path): string => '5a1b552';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testBooleanAttributes(): void
    {
        $o = ['template' => '{input}'];

        $model = new DynamicModel(['name']);

        ob_start();
        $form = ActiveForm::begin(
            [
                'action' => '/something',
                'enableClientScript' => false,
            ],
        );
        ActiveForm::end();
        ob_end_clean();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-dynamicmodel-name">
            <input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" required>
            </div>
            HTML,
            (string) $form
                ->field($model, 'name', $o)
                ->input('email', ['required' => true]),
        );
        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-dynamicmodel-name">
            <input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]">
            </div>
            HTML, (string) $form
                ->field($model, 'name', $o)
                ->input('email', ['required' => false]),
        );
        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-dynamicmodel-name">
            <input type="email" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" required="test">
            </div>
            HTML,
            (string) $form
                ->field($model, 'name', $o)
                ->input('email', ['required' => 'test']),
        );
    }

    public function testIssue5356(): void
    {
        $o = ['template' => '{input}'];

        $model = new DynamicModel(['categories']);

        $model->categories = 1;

        ob_start();
        $form = ActiveForm::begin(
            [
                'action' => '/something',
                'enableClientScript' => false,
            ],
        );
        ActiveForm::end();
        ob_end_clean();

        // https://github.com/yiisoft/yii2/issues/5356
        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-dynamicmodel-categories">
            <input type="hidden" name="DynamicModel[categories]" value=""><select id="dynamicmodel-categories" class="form-control" name="DynamicModel[categories][]" multiple size="4">
            <option value="0">apple</option>
            <option value="1" selected>banana</option>
            <option value="2">avocado</option>
            </select>
            </div>
            HTML,
            (string) $form
                ->field($model, 'categories', $o)
                ->listBox(['apple', 'banana', 'avocado'], ['multiple' => true]),
        );
    }

    public function testOutputBuffering(): void
    {
        $obLevel = ob_get_level();
        ob_start();
        $model = new DynamicModel(['name']);
        $form = ActiveForm::begin(['id' => 'someform', 'action' => '/someform', 'enableClientScript' => false]);
        echo "\n" . $form->field($model, 'name') . "\n";
        ActiveForm::end();
        $content = ob_get_clean();

        $csrfToken = Yii::$app->request->csrfToken;

        $this->assertEquals(
            $obLevel,
            ob_get_level(),
            'Output buffers not closed correctly.',
        );
        $this->assertEqualsWithoutLE(
            <<<HTML
            <form id="someform" action="/someform" method="post">
            <input type="hidden" name="_csrf" value="{$csrfToken}">
            <div class="form-group field-dynamicmodel-name">
            <label class="control-label" for="dynamicmodel-name">Name</label>
            <input type="text" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]">

            <div class="help-block"></div>
            </div>
            </form>
            HTML,
            $content,
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15536
     */
    public function testShouldTriggerInitEvent(): void
    {
        $initTriggered = false;

        ob_start();
        $form = ActiveForm::begin(
            [
                'action' => '/something',
                'enableClientScript' => false,
                'on init' => function () use (&$initTriggered): void {
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
    public function testValidationStateOnInput(): void
    {
        $model = new DynamicModel(['name']);

        $model->addError('name', 'I have an error!');

        ob_start();
        $form = ActiveForm::begin(
            [
                'action' => '/something',
                'enableClientScript' => false,
                'validationStateOn' => ActiveForm::VALIDATION_STATE_ON_INPUT,
            ],
        );
        ActiveForm::end();
        ob_end_clean();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-dynamicmodel-name">
            <label class="control-label" for="dynamicmodel-name">Name</label>
            <input type="text" id="dynamicmodel-name" class="form-control has-error" name="DynamicModel[name]" aria-invalid="true">

            <div class="help-block">I have an error!</div>
            </div>
            HTML,
            (string) $form->field($model, 'name'),
        );
        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-dynamicmodel-name">

            <input type="hidden" name="DynamicModel[name]" value="0"><label><input type="checkbox" id="dynamicmodel-name" class="has-error" name="DynamicModel[name]" value="1" aria-invalid="true"> Name</label>

            <div class="help-block">I have an error!</div>
            </div>
            HTML,
            (string) $form->field($model, 'name')->checkbox(),
        );
        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="form-group field-dynamicmodel-name">

            <input type="hidden" name="DynamicModel[name]" value="0"><label><input type="radio" id="dynamicmodel-name" class="has-error" name="DynamicModel[name]" value="1" aria-invalid="true"> Name</label>

            <div class="help-block">I have an error!</div>
            </div>
            HTML,
            (string) $form->field($model, 'name')->radio(),
        );
    }

    public function testFieldConfigAsClosure(): void
    {
        $model = new DynamicModel(['name', 'email']);

        $closureParams = [];

        ob_start();
        $form = ActiveForm::begin(
            [
                'action' => '/something',
                'fieldConfig' => function ($passedModel, $passedAttribute) use (&$closureParams): array {
                    $closureParams[] = [
                        'model' => $passedModel,
                        'attribute' => $passedAttribute
                    ];

                    if ($passedAttribute === 'email') {
                        return [
                            'template' => '<div class="email-field">{label}{input}{error}</div>',
                        ];
                    }

                    return [
                        'template' => '<div class="default-field">{label}{input}{error}</div>',
                    ];
                },
                'id' => 'w0',
            ],
        );
        echo $form->field($model, 'name');
        echo $form->field($model, 'email');
        ActiveForm::end();
        $expectedForm = ob_get_clean();

        $csrfToken = Yii::$app->request->csrfToken;

        $this->assertEqualsWithoutLE(
            <<<HTML
            <form id="w0" action="/something" method="post">
            <input type="hidden" name="_csrf" value="{$csrfToken}"><div class="form-group field-dynamicmodel-name">
            <div class="default-field"><label class="control-label" for="dynamicmodel-name">Name</label><input type="text" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]"><div class="help-block"></div></div>
            </div><div class="form-group field-dynamicmodel-email">
            <div class="email-field"><label class="control-label" for="dynamicmodel-email">Email</label><input type="text" id="dynamicmodel-email" class="form-control" name="DynamicModel[email]"><div class="help-block"></div></div>
            </div></form>
            HTML,
            $expectedForm,
            'Failed asserting that the generated form matches the expected form.',
        );
    }

    public function testErrorSummary(): void
    {
        $model = new DynamicModel(['name', 'email']);

        $model->addError('name', 'Name cannot be blank.');
        $model->addError('email', 'Email is not a valid email address.');
        $model->addError('email', 'Email cannot be blank.');

        ob_start();
        $form = ActiveForm::begin(['action' => '/something']);
        ActiveForm::end();
        ob_end_clean();

        $this->assertEqualsWithoutLE(
            <<<HTML
            <div class="error-summary"><p>Please fix the following errors:</p><ul><li>Name cannot be blank.</li>
            <li>Email is not a valid email address.</li></ul></div>
            HTML,
            $form->errorSummary($model),
            'Failed asserting that the generated error summary matches the expected error summary.',
        );
    }

    public function testEndField(): void
    {
        $model = new DynamicModel(['name']);

        ob_start();
        $form = ActiveForm::begin(
            [
                'action' => '/something',
                'id' => 'w0',
            ],
        );
        $form->beginField($model, 'name');
        echo '<input type="text" name="test">';
        $form->endField();
        ActiveForm::end();
        $content = ob_get_clean();

        $csrfToken = Yii::$app->request->csrfToken;

        $this->assertEqualsWithoutLE(
            <<<HTML
            <form id="w0" action="/something" method="post">
            <input type="hidden" name="_csrf" value="{$csrfToken}"><input type="text" name="test"></form>
            HTML,
            $content,
            'Failed asserting that the generated form matches the expected form.',
        );
    }

    public function testRegisterClientScript(): void
    {
        $model = new DynamicModel(['name']);

        $model->addRule(['name'], 'required');

        $view = Yii::$app->getView();

        ob_start();
        ob_implicit_flush(false);
        $form = ActiveForm::begin(
            [
                'id' => 'w0',
                'view' => $view,
                'validateOnSubmit' => false,
                'validationUrl' => '/custom/validation',
            ],
        );
        echo $form->field($model, 'name');
        $form::end();
        $expectedForm = ob_get_clean();

        $csrfToken = Yii::$app->request->csrfToken;
        $validate = '"validate":function (attribute, value, messages, deferred, $form) {yii.validation.required(value, messages, {"message":"Name cannot be blank."});}';

        $this->assertEqualsWithoutLE(
            <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test</title>
                </head>
            <body>

            <form id="w0" action="/" method="post">
            <input type="hidden" name="_csrf" value="{$csrfToken}"><div class="form-group field-dynamicmodel-name required">
            <label class="control-label" for="dynamicmodel-name">Name</label>
            <input type="text" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" aria-required="true">

            <div class="help-block"></div>
            </div></form>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.validation.js"></script>
            <script src="/assets/5a1b552/yii.activeForm.js"></script>
            <script>jQuery(function ($) {
            jQuery('#w0').yiiActiveForm([{"id":"dynamicmodel-name","name":"name","container":".field-dynamicmodel-name","input":"#dynamicmodel-name",$validate}], {"validateOnSubmit":false,"validationUrl":"\/custom\/validation"});
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => $expectedForm]),
            'Failed asserting that the generated form matches the expected view.',
        );
    }

    public function testValidate(): void
    {
        $model = new DynamicModel(['name', 'email']);

        $model->addRule(['name'], 'required');
        $model->addRule(['email'], 'email');

        $model->name = '';
        $model->email = 'invalid-email';

        $this->assertSame(
            [
                'dynamicmodel-name' => ['Name cannot be blank.'],
                'dynamicmodel-email' => ['Email is not a valid email address.'],
            ],
            ActiveForm::validate($model),
            'Failed asserting that the validation errors match the expected errors.',
        );
    }

    public function testValidateWhenAttributesParameterIsModel(): void
    {
        $model1 = new DynamicModel(['name']);

        $model1->addRule(['name'], 'required');

        $model1->name = '';

        $model2 = new DynamicModel(['email']);

        $model2->addRule(['email'], 'email');

        $model2->email = 'invalid-email';

        $this->assertSame(
            [
                'dynamicmodel-name' => ['Name cannot be blank.'],
                'dynamicmodel-email' => ['Email is not a valid email address.'],
            ],
            ActiveForm::validate($model1, $model2),
            'Failed asserting that the validation errors match the expected errors.',
        );
    }

    public function testValidateMultiple(): void
    {
        $model1 = new DynamicModel(['name']);

        $model1->addRule(['name'], 'required');

        $model1->name = '';

        $model2 = new DynamicModel(['email']);

        $model2->addRule(['email'], 'email');

        $model2->email = 'invalid-email';

        $this->assertSame(
            [
                'dynamicmodel-0-name' => ['Name cannot be blank.'],
                'dynamicmodel-1-email' => ['Email is not a valid email address.'],
            ],
            ActiveForm::validateMultiple(
                [
                    $model1,
                    $model2,
                ]
            ),
            'Failed asserting that the validation errors match the expected errors.',
        );
    }

    public function testThrowExceptionWhenBeginFieldWithoutEndField(): void
    {
        $ob_level = ob_get_level();
        ob_start();

        $model = new DynamicModel(['name']);
        $form = ActiveForm::begin();

        try {
            $form->beginField($model, 'name');
            $form->run();
        } catch (InvalidCallException $e) {
            $this->assertSame(
                'Each beginField() should have a matching endField() call.',
                $e->getMessage(),
                'Failed asserting error message of thrown exception.',
            );
        }

        while (ob_get_level() > $ob_level) {
            ob_end_clean();
        }
    }

    public function testThrowExceptionWhenEndFieldWithoutBeginField(): void
    {
        $ob_level = ob_get_level();
        ob_start();

        $form = ActiveForm::begin();

        try {
            $form->endField();
        } catch (InvalidCallException $e) {
            $this->assertSame(
                'Mismatching endField() call.',
                $e->getMessage(),
                'Failed asserting error message of thrown exception.',
            );

            while (ob_get_level() > $ob_level) {
                ob_end_clean();
            }
        }
    }
}
