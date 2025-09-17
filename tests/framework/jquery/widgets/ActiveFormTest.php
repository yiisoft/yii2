<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\jquery\widgets;

use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidArgumentException;
use yii\base\Widget;
use yii\web\View;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;

/**
 * @group jquery
 */
final class ActiveFormTest extends \yiiunit\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testRegisterClientScript(): void
    {
        $_SERVER['REQUEST_URI'] = 'http://example.com/';

        $model = new DynamicModel(['name']);

        $model->addRule(['name'], 'required');

        Yii::$app->assetManager->hashCallback = static fn ($path): string => '5a1b552';
        $view = new View();

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
            'Failed asserting that the generated form matches the expected form.',
        );
        $this->assertSame(
            [
                'validateOnSubmit' => false,
                'validationUrl' => '/custom/validation',
            ],
            $form->clientScript->getClientOptions($form),
            "Failed asserting that 'getClientOptions()' returns the expected options.",
        );
    }

    public function testRegisterClientScriptWithUseQueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $model = new DynamicModel(['name']);
        $view = new View();

        ob_start();
        ob_implicit_flush(false);
        $form = ActiveForm::begin(
            [
                'action' => '/something',
                'id' => 'w0',
            ],
        );
        echo $form->field($model, 'name');
        $form::end();
        $expectedForm = ob_get_clean();

        $csrfToken = Yii::$app->request->csrfToken;

        $this->assertNull(
            $form->clientScript,
            "'clientScript' property should be 'null' when 'useJquery' is 'false'.",
        );
        $this->assertEmpty(
            $this->invokeMethod($form, 'getClientOptions'),
            "'getClientOptions()' method should return empty array when 'useJquery' is 'false'.",
        );
        $this->assertEqualsWithoutLE(
            <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test</title>
                </head>
            <body>

            <form id="w0" action="/something" method="post">
            <input type="hidden" name="_csrf" value="$csrfToken"><div class="form-group field-dynamicmodel-name">
            <label class="control-label" for="dynamicmodel-name">Name</label>
            <input type="text" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]">

            <div class="help-block"></div>
            </div></form>
            </body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => $expectedForm]),
            'Failed asserting that the generated form matches the expected form.',
        );
    }

    public function testGetClientOptionsForFieldWithAriaAttributesFalse(): void
    {
        $_SERVER['REQUEST_URI'] = 'http://example.com/';

        $model = new DynamicModel(['name']);

        $model->addRule(['name'], 'required');

        Yii::$app->assetManager->hashCallback = static fn ($path): string => '5a1b552';
        $view = new View();

        ob_start();
        ob_implicit_flush(false);
        $form = ActiveForm::begin(
            [
                'id' => 'w0',
                'view' => $view,
            ],
        );
        $field = $form->field($model, 'name');
        $field->addAriaAttributes = false;
        echo $field;
        $form::end();
        $expectedForm = ob_get_clean();

        $csrfToken = Yii::$app->request->csrfToken;
        $validate = '"validate":function (attribute, value, messages, deferred, $form) {yii.validation.required(value, messages, {"message":"Name cannot be blank."}';

        $this->assertEqualsWithoutLE(
            <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test</title>
                </head>
            <body>

            <form id="w0" action="/" method="post">
            <input type="hidden" name="_csrf" value="$csrfToken"><div class="form-group field-dynamicmodel-name required">
            <label class="control-label" for="dynamicmodel-name">Name</label>
            <input type="text" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]">

            <div class="help-block"></div>
            </div></form>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.validation.js"></script>
            <script src="/assets/5a1b552/yii.activeForm.js"></script>
            <script>jQuery(function ($) {
            jQuery('#w0').yiiActiveForm([{"id":"dynamicmodel-name","name":"name","container":".field-dynamicmodel-name","input":"#dynamicmodel-name",$validate);},"updateAriaInvalid":false}], []);
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => $expectedForm]),
            'Failed asserting that the generated form matches the expected form.',
        );
        $this->assertFalse(
            $form->clientScript->getClientOptions($field)['updateAriaInvalid'] ?? null,
            "Failed asserting that 'getClientOptions()' returns the expected options.",
        );
    }

    public function testGetClientOptionsForFieldWithCustomErrorSelector(): void
    {
        $_SERVER['REQUEST_URI'] = 'http://example.com/';

        $model = new DynamicModel(['name']);

        $model->addRule(['name'], 'required');

        Yii::$app->assetManager->hashCallback = static fn ($path): string => '5a1b552';
        $view = new View();

        ob_start();
        ob_implicit_flush(false);
        $form = ActiveForm::begin(
            [
                'id' => 'w0',
                'view' => $view,
            ],
        );
        $field = $form->field($model, 'name');
        $field->selectors = ['error' => '.custom-error-selector'];
        echo $field;
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
            <input type="hidden" name="_csrf" value="$csrfToken"><div class="form-group field-dynamicmodel-name required">
            <label class="control-label" for="dynamicmodel-name">Name</label>
            <input type="text" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" aria-required="true">

            <div class="help-block"></div>
            </div></form>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.validation.js"></script>
            <script src="/assets/5a1b552/yii.activeForm.js"></script>
            <script>jQuery(function ($) {
            jQuery('#w0').yiiActiveForm([{"id":"dynamicmodel-name","name":"name","container":".field-dynamicmodel-name","input":"#dynamicmodel-name","error":".custom-error-selector",$validate}], []);
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => $expectedForm]),
            'Failed asserting that the generated form matches the expected form.',
        );
    }

    public function testGetClientOptionsForFieldWithDefaultTag(): void
    {
        $_SERVER['REQUEST_URI'] = 'http://example.com/';

        $model = new DynamicModel(['name']);

        $model->addRule(['name'], 'string');

        Yii::$app->assetManager->hashCallback = static fn ($path): string => '5a1b552';
        $view = new View();

        ob_start();
        ob_implicit_flush(false);
        $form = ActiveForm::begin([
            'id' => 'w0',
            'view' => $view,
        ]);
        $field = $form->field($model, 'name');
        unset($field->selectors['error']);
        $field->errorOptions = [];
        echo $field;
        $form::end();
        $expectedForm = ob_get_clean();

        $csrfToken = Yii::$app->request->csrfToken;
        $validate = '"validate":function (attribute, value, messages, deferred, $form) {yii.validation.string(value, messages, {"message":"Name must be a string.","skipOnEmpty":1';

        $this->assertEqualsWithoutLE(
            <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test</title>
                </head>
            <body>

            <form id="w0" action="/" method="post">
            <input type="hidden" name="_csrf" value="$csrfToken"><div class="form-group field-dynamicmodel-name">
            <label class="control-label" for="dynamicmodel-name">Name</label>
            <input type="text" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]">

            <div></div>
            </div></form>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.validation.js"></script>
            <script src="/assets/5a1b552/yii.activeForm.js"></script>
            <script>jQuery(function ($) {
            jQuery('#w0').yiiActiveForm([{"id":"dynamicmodel-name","name":"name","container":".field-dynamicmodel-name","input":"#dynamicmodel-name","error":"span",$validate});}}], []);
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => $expectedForm]),
            'Failed asserting that the generated form matches the expected form.',
        );
    }

    public function testGetClientOptionsForFieldWithErrorOptionsClass(): void
    {
        $_SERVER['REQUEST_URI'] = 'http://example.com/';

        $model = new DynamicModel(['name']);

        $model->addRule(['name'], 'required');

        Yii::$app->assetManager->hashCallback = static fn ($path): string => '5a1b552';
        $view = new View();

        ob_start();
        ob_implicit_flush(false);
        $form = ActiveForm::begin(
            [
                'id' => 'w0',
                'view' => $view,
            ],
        );
        $field = $form->field($model, 'name');
        unset($field->selectors['error']);
        $field->errorOptions = ['class' => 'error-class another-class'];
        echo $field;
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
            <input type="hidden" name="_csrf" value="$csrfToken"><div class="form-group field-dynamicmodel-name required">
            <label class="control-label" for="dynamicmodel-name">Name</label>
            <input type="text" id="dynamicmodel-name" class="form-control" name="DynamicModel[name]" aria-required="true">

            <div class="error-class another-class"></div>
            </div></form>
            <script src="/assets/5a1b552/jquery.js"></script>
            <script src="/assets/5a1b552/yii.js"></script>
            <script src="/assets/5a1b552/yii.validation.js"></script>
            <script src="/assets/5a1b552/yii.activeForm.js"></script>
            <script>jQuery(function ($) {
            jQuery('#w0').yiiActiveForm([{"id":"dynamicmodel-name","name":"name","container":".field-dynamicmodel-name","input":"#dynamicmodel-name","error":".error-class.another-class",$validate}], []);
            });</script></body>
            </html>

            HTML,
            $view->render('@yiiunit/data/views/layout.php', ['content' => $expectedForm]),
            'Failed asserting that the generated form matches the expected form.',
        );
    }

    public function testThrowExceptionWhenGetClientOptionsWithWrongObject(): void
    {
        $ob_level = ob_get_level();
        ob_start();

        $view = new View();
        $form = new ActiveForm(
            [
                'id' => 'test-form',
                'view' => $view,
            ],
        );

        try {
            $form->clientScript->getClientOptions(new Widget());
        } catch (InvalidArgumentException $e) {
            $this->assertSame(
                'Object must be an instance of ' . ActiveForm::class . ' or ' . ActiveField::class . '.',
                $e->getMessage(),
            );

            while (ob_get_level() > $ob_level) {
                ob_end_clean();
            }
        }
    }
}
