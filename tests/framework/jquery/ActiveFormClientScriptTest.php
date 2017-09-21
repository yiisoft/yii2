<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\jquery;

use Yii;
use yii\base\DynamicModel;
use yii\jquery\ActiveFormAsset;
use yii\jquery\ActiveFormClientScript;
use yii\web\View;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;
use yiiunit\TestCase;

/**
 * @group jquery
 */
class ActiveFormClientScriptTest extends TestCase
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
    /**
     * @var string
     */
    private $attributeName = 'attributeName';


    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        // dirty way to have Request object not throwing exception when running testHomeLinkNull()
        $_SERVER['SCRIPT_FILENAME'] = "index.php";
        $_SERVER['SCRIPT_NAME'] = "index.php";

        $this->mockWebApplication([
            'components' => [
                'assetManager' => [
                    'basePath' => '@testWebRoot/assets',
                    'baseUrl' => '@testWeb/assets',
                    'bundles' => [
                        ActiveFormAsset::class => [
                            'sourcePath' => null,
                            'basePath' => null,
                            'baseUrl' => 'http://example.com/assets',
                            'depends' => [],
                        ],
                    ],
                ],
            ],
        ]);

        Yii::setAlias('@testWeb', '/');
        Yii::setAlias('@testWebRoot', '@yiiunit/data/web');

        $this->helperModel = new DynamicModel(['attributeName']);
        ob_start();
        $this->helperForm = ActiveForm::begin([
            'action' => '/something',
            'as clientScript' => ActiveFormClientScript::class
        ]);
        ActiveForm::end();
        ob_end_clean();

        $this->activeField = new ActiveField();
        $this->activeField->form = $this->helperForm;
        $this->activeField->model = $this->helperModel;
        $this->activeField->attribute = $this->attributeName;
    }

    /**
     * @return array client options of current [[activeField]] instance.
     */
    protected function getActiveFieldClientOptions()
    {
        // invoke protected method :
        return $this->invokeMethod($this->activeField->form->getBehavior('clientScript'), 'getFieldClientOptions', [$this->activeField]);
    }

    // Tests :

    public function testRegisterClientScript()
    {
        $model = new DynamicModel(['name']);
        $model->addRule(['name'], 'required');

        $mockedView = $this->createMock(View::class);
        $mockedView
            ->expects($this->once())
            ->method('registerJs')
            ->with($this->matches("jQuery('#test-form').yiiActiveForm([], {\"validateOnSubmit\":false});"));
        $mockedView
            ->expects($this->once())
            ->method('registerAssetBundle')
            ->willReturn(true);

        ob_start();
        ob_implicit_flush(false);

        $form = ActiveForm::begin([
            'id' => 'test-form',
            'action' => '/something',
            'view' => $mockedView,
            'validateOnSubmit' => false,
            'as clientScript' => ActiveFormClientScript::class,
        ]);
        $form->field($model, 'name');
        $form::end();

        ob_get_clean();
    }

    public function testGetClientOptionsWithActiveAttributeInScenario()
    {
        $this->activeField->model->addRule($this->attributeName, TestValidator::class);
        $this->activeField->form->enableClientValidation = false;

        // expected empty
        $actualValue = $this->getActiveFieldClientOptions();
        $this->assertEmpty($actualValue);

    }

    public function testGetClientOptionsClientValidation()
    {
        $this->activeField->model->addRule($this->attributeName, TestValidator::class);
        $this->activeField->enableClientValidation = true;
        $actualValue = $this->getActiveFieldClientOptions();
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

        $actualValue = $this->getActiveFieldClientOptions();

        $this->assertTrue($expectedValidateOnChange === $actualValue['validateOnChange']);
        $this->assertTrue($expectedValidateOnBlur === $actualValue['validateOnBlur']);
        $this->assertTrue($expectedValidateOnType === $actualValue['validateOnType']);
        $this->assertTrue($expectedValidationDelay === $actualValue['validationDelay']);
        $this->assertTrue($expectedEnableAjaxValidation === $actualValue['enableAjaxValidation']);
    }

    public function testGetClientOptionsValidatorWhenClientSet()
    {
        $this->activeField->enableAjaxValidation = true;
        $this->activeField->model->addRule($this->attributeName, TestValidator::class);

        foreach($this->activeField->model->validators as $validator) {
            $validator->whenClient = "function (attribute, value) { return 'yii2' == 'yii2'; }"; // js
        }

        $actualValue = $this->getActiveFieldClientOptions();
        $expectedJsExpression = "function (attribute, value, messages, deferred, \$form) {if ((function (attribute, value) "
            . "{ return 'yii2' == 'yii2'; })(attribute, value)) { return true; }}";

        $this->assertEquals($expectedJsExpression, $actualValue['validate']->expression);
    }

    /**
     * @link https://github.com/yiisoft/yii2/issues/7627
     */
    public function testGetClientOptionsWithCustomInputId()
    {
        $this->activeField->model->addRule($this->attributeName, TestValidator::class);
        $this->activeField->inputOptions['id'] = 'custom-input-id';
        $this->activeField->textInput();
        $actualValue = $this->getActiveFieldClientOptions();

        $this->assertArraySubset([
            'id' => 'dynamicmodel-attributename',
            'name' => $this->attributeName,
            'container' => '.field-custom-input-id',
            'input' => '#custom-input-id',
        ], $actualValue);

        $this->activeField->textInput(['id' => 'custom-textinput-id']);
        $actualValue = $this->getActiveFieldClientOptions();

        $this->assertArraySubset([
            'id' => 'dynamicmodel-attributename',
            'name' => $this->attributeName,
            'container' => '.field-custom-textinput-id',
            'input' => '#custom-textinput-id',
        ], $actualValue);
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