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
use yii\base\InvalidConfigException;
use yiiunit\TestCase;

class CaptchaActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication([
            'controllerMap' => [
                'test' => 'yii\web\Controller',
            ],
            'components' => [
                'session' => [
                    'class' => CaptchaTestSession::class,
                ],
            ],
        ]);
    }

    private function createAction(array $config = []): CaptchaAction
    {
        $controller = Yii::$app->createController('test')[0];
        $action = new CaptchaAction('captcha', $controller, $config);
        return $action;
    }

    public function testInitWithValidFontFile(): void
    {
        $action = $this->createAction();
        $this->assertFileExists($action->fontFile);
    }

    public function testInitWithInvalidFontFileThrowsException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->createAction(['fontFile' => '/nonexistent/font.ttf']);
    }

    /**
     * @dataProvider generateValidationHashProvider
     */
    public function testGenerateValidationHash(string $code, int $expectedHash): void
    {
        $action = $this->createAction();
        $this->assertSame($expectedHash, $action->generateValidationHash($code));
    }

    public static function generateValidationHashProvider(): array
    {
        return [
            'single char' => ['a', ord('a')],
            'two chars' => ['ab', ord('a') + (ord('b') << 1)],
            'test' => ['test', ord('t') + (ord('e') << 1) + (ord('s') << 2) + (ord('t') << 3)],
        ];
    }

    public function testGenerateValidationHashIsDeterministic(): void
    {
        $action = $this->createAction();
        $hash1 = $action->generateValidationHash('hello');
        $hash2 = $action->generateValidationHash('hello');
        $this->assertSame($hash1, $hash2);
    }

    public function testGenerateValidationHashDifferentStringsDifferentHashes(): void
    {
        $action = $this->createAction();
        $hash1 = $action->generateValidationHash('abc');
        $hash2 = $action->generateValidationHash('xyz');
        $this->assertNotSame($hash1, $hash2);
    }

    public function testGenerateValidationHashIsCaseSensitive(): void
    {
        $action = $this->createAction();
        $hashLower = $action->generateValidationHash('abc');
        $hashUpper = $action->generateValidationHash('ABC');
        $this->assertNotSame($hashLower, $hashUpper);
    }

    public function testGetVerifyCodeWithFixedCode(): void
    {
        $action = $this->createAction(['fixedVerifyCode' => 'testme']);
        $this->assertSame('testme', $action->getVerifyCode());
    }

    public function testGetVerifyCodeWithFixedCodeIgnoresRegenerate(): void
    {
        $action = $this->createAction(['fixedVerifyCode' => 'testme']);
        $this->assertSame('testme', $action->getVerifyCode(true));
    }

    public function testGetVerifyCodeFromSession(): void
    {
        $action = $this->createAction();
        $code1 = $action->getVerifyCode();
        $code2 = $action->getVerifyCode();
        $this->assertSame($code1, $code2);
    }

    public function testGetVerifyCodeRegeneratesWhenRequested(): void
    {
        $action = $this->createAction();
        $codes = [];
        for ($i = 0; $i < 20; $i++) {
            $codes[] = $action->getVerifyCode(true);
        }
        $this->assertGreaterThan(1, count(array_unique($codes)));
    }

    public function testGenerateVerifyCodeRespectsDefaultLengthBounds(): void
    {
        $action = $this->createAction();
        for ($i = 0; $i < 10; $i++) {
            $code = $this->invokeMethod($action, 'generateVerifyCode');
            $length = strlen($code);
            $this->assertGreaterThanOrEqual(6, $length);
            $this->assertLessThanOrEqual(7, $length);
        }
    }

    public function testGenerateVerifyCodeRespectsCustomLengthBounds(): void
    {
        $action = $this->createAction(['minLength' => 8, 'maxLength' => 10]);
        for ($i = 0; $i < 10; $i++) {
            $code = $this->invokeMethod($action, 'generateVerifyCode');
            $length = strlen($code);
            $this->assertGreaterThanOrEqual(8, $length);
            $this->assertLessThanOrEqual(10, $length);
        }
    }

    public function testGenerateVerifyCodeClampsMinLengthBelow3(): void
    {
        $action = $this->createAction(['minLength' => 2, 'maxLength' => 5]);
        $code = $this->invokeMethod($action, 'generateVerifyCode');
        $this->assertGreaterThanOrEqual(3, strlen($code));
        $this->assertLessThanOrEqual(5, strlen($code));
    }

    public function testGenerateVerifyCodeDoesNotClampMinLengthAt3(): void
    {
        $action = $this->createAction(['minLength' => 3, 'maxLength' => 3]);
        $code = $this->invokeMethod($action, 'generateVerifyCode');
        $this->assertSame(3, strlen($code));
    }

    public function testGenerateVerifyCodeClampsMaxLengthAbove20(): void
    {
        $action = $this->createAction(['minLength' => 18, 'maxLength' => 21]);
        $code = $this->invokeMethod($action, 'generateVerifyCode');
        $this->assertLessThanOrEqual(20, strlen($code));
        $this->assertGreaterThanOrEqual(18, strlen($code));
    }

    public function testGenerateVerifyCodeDoesNotClampMaxLengthAt20(): void
    {
        $action = $this->createAction(['minLength' => 20, 'maxLength' => 20]);
        $code = $this->invokeMethod($action, 'generateVerifyCode');
        $this->assertSame(20, strlen($code));
    }

    public function testGenerateVerifyCodeAdjustsMaxLengthWhenLessThanMinLength(): void
    {
        $action = $this->createAction(['minLength' => 10, 'maxLength' => 5]);
        $code = $this->invokeMethod($action, 'generateVerifyCode');
        $this->assertSame(10, strlen($code));
    }

    public function testGenerateVerifyCodeEqualMinAndMaxLength(): void
    {
        $action = $this->createAction(['minLength' => 8, 'maxLength' => 8]);
        $code = $this->invokeMethod($action, 'generateVerifyCode');
        $this->assertSame(8, strlen($code));
    }

    public function testGenerateVerifyCodeContainsOnlyValidChars(): void
    {
        $action = $this->createAction();
        $validChars = 'bcdfghjklmnpqrstvwxyzaeiou';
        for ($i = 0; $i < 10; $i++) {
            $code = $this->invokeMethod($action, 'generateVerifyCode');
            $this->assertMatchesRegularExpression('/^[' . $validChars . ']+$/', $code);
        }
    }

    public function testGetSessionKeyFormat(): void
    {
        $action = $this->createAction();
        $sessionKey = $this->invokeMethod($action, 'getSessionKey');
        $this->assertSame('__captcha/' . $action->getUniqueId(), $sessionKey);
    }

    public function testValidateCorrectCodeCaseSensitive(): void
    {
        $action = $this->createAction(['fixedVerifyCode' => 'TestCode']);
        $this->assertTrue($action->validate('TestCode', true));
    }

    public function testValidateWrongCodeCaseSensitive(): void
    {
        $action = $this->createAction(['fixedVerifyCode' => 'TestCode']);
        $this->assertFalse($action->validate('wrongcode', true));
    }

    public function testValidateCaseSensitiveRejectsDifferentCase(): void
    {
        $action = $this->createAction(['fixedVerifyCode' => 'TestCode']);
        $this->assertFalse($action->validate('testcode', true));
    }

    public function testValidateCaseInsensitiveAcceptsDifferentCase(): void
    {
        $action = $this->createAction(['fixedVerifyCode' => 'TestCode']);
        $this->assertTrue($action->validate('testcode', false));
    }

    public function testValidateCaseInsensitiveRejectsWrongCode(): void
    {
        $action = $this->createAction(['fixedVerifyCode' => 'TestCode']);
        $this->assertFalse($action->validate('wrongcode', false));
    }

    public function testValidateRegeneratesCodeAfterTestLimit(): void
    {
        $action = $this->createAction(['testLimit' => 2]);

        $code = $action->getVerifyCode();

        $action->validate('wrong', true);
        $codeAfterFirstFail = $action->getVerifyCode();
        $this->assertSame($code, $codeAfterFirstFail);

        $action->validate('wrong', true);
        $codeAfterExceedingLimit = $action->getVerifyCode();
        $this->assertNotSame($code, $codeAfterExceedingLimit);
    }

    public function testValidateWithUnlimitedTestLimit(): void
    {
        $action = $this->createAction(['testLimit' => 0]);

        $code = $action->getVerifyCode();

        for ($i = 0; $i < 5; $i++) {
            $action->validate('wrong', true);
        }

        $this->assertSame($code, $action->getVerifyCode());
    }

    public function testRunWithRefreshReturnsJsonStructure(): void
    {
        $_GET[CaptchaAction::REFRESH_GET_VAR] = 1;

        try {
            $action = $this->createAction(['fixedVerifyCode' => 'testme']);
            Yii::$app->controller = $action->controller;
            $result = $action->run();

            $this->assertArrayHasKey('hash1', $result);
            $this->assertArrayHasKey('hash2', $result);
            $this->assertArrayHasKey('url', $result);
            $this->assertSame($action->generateValidationHash('testme'), $result['hash1']);
            $this->assertSame($action->generateValidationHash('testme'), $result['hash2']);
        } finally {
            unset($_GET[CaptchaAction::REFRESH_GET_VAR]);
        }
    }

    public function testRunWithRefreshRegeneratesCode(): void
    {
        $action = $this->createAction();
        Yii::$app->controller = $action->controller;
        $codeBefore = $action->getVerifyCode();

        $_GET[CaptchaAction::REFRESH_GET_VAR] = 1;

        try {
            $result = $action->run();
            $codeAfter = $action->getVerifyCode();

            $this->assertNotSame($codeBefore, $codeAfter);
            $this->assertSame($action->generateValidationHash($codeAfter), $result['hash1']);
        } finally {
            unset($_GET[CaptchaAction::REFRESH_GET_VAR]);
        }
    }

    public function testRunWithRefreshHash2UsesLowercase(): void
    {
        $_GET[CaptchaAction::REFRESH_GET_VAR] = 1;

        try {
            $action = $this->createAction(['fixedVerifyCode' => 'TestMe']);
            Yii::$app->controller = $action->controller;
            $result = $action->run();

            $this->assertSame($action->generateValidationHash('TestMe'), $result['hash1']);
            $this->assertSame($action->generateValidationHash('testme'), $result['hash2']);
            $this->assertNotSame($result['hash1'], $result['hash2']);
        } finally {
            unset($_GET[CaptchaAction::REFRESH_GET_VAR]);
        }
    }

    public function testSetHttpHeadersSetsAllRequiredHeaders(): void
    {
        $action = $this->createAction();
        $this->invokeMethod($action, 'setHttpHeaders');

        $headers = Yii::$app->response->headers;
        $this->assertSame('public', $headers->get('Pragma'));
        $this->assertSame('0', $headers->get('Expires'));
        $this->assertSame('must-revalidate, post-check=0, pre-check=0', $headers->get('Cache-Control'));
        $this->assertSame('binary', $headers->get('Content-Transfer-Encoding'));
        $this->assertSame('image/png', $headers->get('Content-type'));
    }

    public function testRunWithoutRefreshReturnsImageAndSetsHeaders(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is required.');
        }

        $action = $this->createAction(['fixedVerifyCode' => 'testme']);
        $action->imageLibrary = 'gd';
        $result = $action->run();

        $this->assertNotEmpty($result);

        $headers = Yii::$app->response->headers;
        $this->assertSame('image/png', $headers->get('Content-type'));
        $this->assertSame('binary', $headers->get('Content-Transfer-Encoding'));
    }

    public function testRenderImageByGDReturnsPngData(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is required.');
        }

        $action = $this->createAction();
        $action->imageLibrary = 'gd';
        $imageData = $this->invokeMethod($action, 'renderImage', ['testme']);

        $this->assertStringStartsWith("\x89PNG", $imageData);
    }

    public function testRenderImageByGDWithTransparentBackground(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is required.');
        }

        $action = $this->createAction(['transparent' => true]);
        $action->imageLibrary = 'gd';
        $imageData = $this->invokeMethod($action, 'renderImage', ['testme']);

        $this->assertStringStartsWith("\x89PNG", $imageData);
    }

    public function testRenderImageThrowsOnUnsupportedLibrary(): void
    {
        $action = $this->createAction(['fixedVerifyCode' => 'test']);
        $action->imageLibrary = 'unsupported';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage("Defined library 'unsupported' is not supported");
        $this->invokeMethod($action, 'renderImage', ['test']);
    }
}
