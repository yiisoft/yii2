<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\captcha;

use Yii;
use yii\captcha\CaptchaAction;
use yii\captcha\CaptchaValidator;
use yii\base\InvalidConfigException;
use yii\web\Controller;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

class CaptchaValidatorTestController extends Controller
{
    public function actions()
    {
        return [
            'captcha' => [
                'class' => CaptchaAction::className(),
                'fixedVerifyCode' => 'testme',
            ],
        ];
    }
}

/**
 * @group captcha
 */
class CaptchaValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication([
            'controllerMap' => [
                'test' => CaptchaValidatorTestController::class,
            ],
            'components' => [
                'session' => [
                    'class' => CaptchaTestSession::class,
                ],
            ],
        ]);
    }

    private function createValidator(array $config = []): CaptchaValidator
    {
        $defaults = ['captchaAction' => 'test/captcha'];
        return new CaptchaValidator(array_merge($defaults, $config));
    }

    public function testInitSetsDefaultMessage(): void
    {
        $validator = $this->createValidator();
        $this->assertSame('The verification code is incorrect.', $validator->message);
    }

    public function testInitPreservesCustomMessage(): void
    {
        $validator = $this->createValidator(['message' => 'Custom error']);
        $this->assertSame('Custom error', $validator->message);
    }

    public function testValidateCorrectValue(): void
    {
        $validator = $this->createValidator();
        $result = $this->invokeMethod($validator, 'validateValue', ['testme']);
        $this->assertNull($result);
    }

    public function testValidateWrongValue(): void
    {
        $validator = $this->createValidator();
        $result = $this->invokeMethod($validator, 'validateValue', ['wrong']);
        $this->assertIsArray($result);
        $this->assertSame('The verification code is incorrect.', $result[0]);
    }

    public function testValidateArrayValueReturnsError(): void
    {
        $validator = $this->createValidator();
        $result = $this->invokeMethod($validator, 'validateValue', [['testme']]);
        $this->assertIsArray($result);
        $this->assertSame('The verification code is incorrect.', $result[0]);
    }

    public function testValidateCaseInsensitiveByDefault(): void
    {
        $validator = $this->createValidator();
        $result = $this->invokeMethod($validator, 'validateValue', ['TESTME']);
        $this->assertNull($result);
    }

    public function testValidateCaseSensitiveRejectsWrongCase(): void
    {
        $validator = $this->createValidator(['caseSensitive' => true]);
        $result = $this->invokeMethod($validator, 'validateValue', ['TESTME']);
        $this->assertIsArray($result);
    }

    public function testValidateCaseSensitiveAcceptsCorrectCase(): void
    {
        $validator = $this->createValidator(['caseSensitive' => true]);
        $result = $this->invokeMethod($validator, 'validateValue', ['testme']);
        $this->assertNull($result);
    }

    public function testCreateCaptchaActionReturnsAction(): void
    {
        $validator = $this->createValidator();
        $action = $validator->createCaptchaAction();
        $this->assertInstanceOf(CaptchaAction::class, $action);
    }

    public function testCreateCaptchaActionThrowsOnInvalidRoute(): void
    {
        $validator = $this->createValidator(['captchaAction' => 'invalid/route']);
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('Invalid CAPTCHA action ID: invalid/route');
        $validator->createCaptchaAction();
    }

    public function testGetClientOptionsContainsRequiredKeys(): void
    {
        $validator = $this->createValidator();
        $model = FakedValidationModel::createWithAttributes(['attr_captcha' => 'testme']);
        $options = $validator->getClientOptions($model, 'attr_captcha');

        $this->assertArrayHasKey('hash', $options);
        $this->assertArrayHasKey('hashKey', $options);
        $this->assertArrayHasKey('caseSensitive', $options);
        $this->assertArrayHasKey('message', $options);
        $this->assertArrayNotHasKey('skipOnEmpty', $options);
    }

    public function testGetClientOptionsCaseSensitiveValue(): void
    {
        $validator = $this->createValidator(['caseSensitive' => true]);
        $model = FakedValidationModel::createWithAttributes(['attr_captcha' => 'testme']);
        $options = $validator->getClientOptions($model, 'attr_captcha');

        $this->assertTrue($options['caseSensitive']);
    }

    public function testGetClientOptionsCaseInsensitiveValue(): void
    {
        $validator = $this->createValidator();
        $model = FakedValidationModel::createWithAttributes(['attr_captcha' => 'testme']);
        $options = $validator->getClientOptions($model, 'attr_captcha');

        $this->assertFalse($options['caseSensitive']);
    }

    public function testGetClientOptionsWithSkipOnEmpty(): void
    {
        $validator = $this->createValidator(['skipOnEmpty' => true]);
        $model = FakedValidationModel::createWithAttributes(['attr_captcha' => 'testme']);
        $options = $validator->getClientOptions($model, 'attr_captcha');

        $this->assertArrayHasKey('skipOnEmpty', $options);
        $this->assertSame(1, $options['skipOnEmpty']);
    }

    public function testGetClientOptionsHashKeyContainsActionId(): void
    {
        $validator = $this->createValidator();
        $model = FakedValidationModel::createWithAttributes(['attr_captcha' => 'testme']);
        $options = $validator->getClientOptions($model, 'attr_captcha');

        $this->assertStringStartsWith('yiiCaptcha/', $options['hashKey']);
    }

    public function testClientValidateAttributeReturnsJsString(): void
    {
        $validator = $this->createValidator();
        $model = FakedValidationModel::createWithAttributes(['attr_captcha' => 'testme']);
        $view = $this->getMockBuilder(View::class)
            ->onlyMethods(['registerAssetBundle'])
            ->getMock();
        $js = $validator->clientValidateAttribute($model, 'attr_captcha', $view);

        $this->assertStringStartsWith('yii.validation.captcha(value, messages, ', $js);
        $this->assertStringEndsWith(');', $js);
    }
}
