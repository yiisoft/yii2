<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\validators\EmailValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\framework\validators\stub\EmailValidatorMockeryFunctionsTrait;
use yiiunit\TestCase;

/**
 * @group validators
 */
class EmailValidatorTest extends TestCase
{
    use EmailValidatorMockeryFunctionsTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetStubs();
        $this->destroyApplication();
    }

    public function testValidateValue(): void
    {
        $validator = new EmailValidator();

        $this->assertTrue($validator->validate('sam@rmcreative.ru'));
        $this->assertTrue($validator->validate('5011@gmail.com'));
        $this->assertTrue($validator->validate('Abc.123@example.com'));
        $this->assertTrue($validator->validate('user+mailbox/department=shipping@example.com'));
        $this->assertTrue($validator->validate('!#$%&\'*+-/=?^_`.{|}~@example.com'));
        $this->assertTrue($validator->validate('firstName.x.lastName.-nd@example.com'));
        $this->assertFalse($validator->validate('rmcreative.ru'));
        $this->assertFalse($validator->validate('Carsten Brandt <mail@cebe.cc>'));
        $this->assertFalse($validator->validate('"Carsten Brandt" <mail@cebe.cc>'));
        $this->assertFalse($validator->validate('<mail@cebe.cc>'));
        $this->assertFalse($validator->validate('info@örtliches.de'));
        $this->assertFalse($validator->validate('sam@рмкреатиф.ru'));
        $this->assertFalse($validator->validate('ex..ample@example.com'));
        $this->assertFalse($validator->validate(['developer@yiiframework.com']));

        $validator->allowName = true;

        $this->assertTrue($validator->validate('sam@rmcreative.ru'));
        $this->assertTrue($validator->validate('5011@gmail.com'));
        $this->assertFalse($validator->validate('rmcreative.ru'));
        $this->assertTrue($validator->validate('Carsten Brandt <mail@cebe.cc>'));
        $this->assertTrue($validator->validate('"Carsten Brandt" <mail@cebe.cc>'));
        $this->assertTrue($validator->validate('<mail@cebe.cc>'));
        $this->assertTrue($validator->validate('"FirstName LastName" <firstName.x.lastName.-nd@example.com>'));
        $this->assertFalse($validator->validate('info@örtliches.de'));
        $this->assertFalse($validator->validate('üñîçøðé@üñîçøðé.com'));
        $this->assertFalse($validator->validate('sam@рмкреатиф.ru'));
        $this->assertFalse($validator->validate('Informtation info@oertliches.de'));
        $this->assertTrue($validator->validate('test@example.com'));
        $this->assertTrue($validator->validate('John Smith <john.smith@example.com>'));
        $this->assertTrue($validator->validate('"This name is longer than 64 characters. Blah blah blah blah blah" <shortmail@example.com>'));
        $this->assertFalse($validator->validate('John Smith <example.com>'));
        $this->assertFalse($validator->validate('Short Name <localPartMoreThan64Characters-blah-blah-blah-blah-blah-blah-blah-blah@example.com>'));
        $this->assertFalse($validator->validate('Short Name <domainNameIsMoreThan254Characters@example-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah.com>'));
        $this->assertFalse($validator->validate(['developer@yiiframework.com']));

    }

    public function testValidateValueIdn(): void
    {
        if (!function_exists('idn_to_ascii')) {
            $this->markTestSkipped('Intl extension required');
        }

        $validator = new EmailValidator();
        $validator->enableIDN = true;

        $this->assertTrue($validator->validate('5011@example.com'));
        $this->assertTrue($validator->validate('example@äüößìà.de'));
        $this->assertTrue($validator->validate('example@xn--zcack7ayc9a.de'));
        $this->assertTrue($validator->validate('info@örtliches.de'));
        $this->assertTrue($validator->validate('sam@рмкреатиф.ru'));
        $this->assertTrue($validator->validate('sam@rmcreative.ru'));
        $this->assertTrue($validator->validate('5011@gmail.com'));
        $this->assertTrue($validator->validate('üñîçøðé@üñîçøðé.com'));
        $this->assertTrue($validator->validate('firstName.x.lastName.-nd@example.com'));
        $this->assertFalse($validator->validate('rmcreative.ru'));
        $this->assertFalse($validator->validate('Carsten Brandt <mail@cebe.cc>'));
        $this->assertFalse($validator->validate('"Carsten Brandt" <mail@cebe.cc>'));
        $this->assertFalse($validator->validate('<mail@cebe.cc>'));

        $validator->allowName = true;

        $this->assertTrue($validator->validate('info@örtliches.de'));
        $this->assertTrue($validator->validate('Informtation <info@örtliches.de>'));
        $this->assertFalse($validator->validate('Informtation info@örtliches.de'));
        $this->assertTrue($validator->validate('sam@рмкреатиф.ru'));
        $this->assertTrue($validator->validate('sam@rmcreative.ru'));
        $this->assertTrue($validator->validate('5011@gmail.com'));
        $this->assertFalse($validator->validate('rmcreative.ru'));
        $this->assertTrue($validator->validate('Carsten Brandt <mail@cebe.cc>'));
        $this->assertTrue($validator->validate('"Carsten Brandt" <mail@cebe.cc>'));
        $this->assertTrue($validator->validate('üñîçøðé 日本国 <üñîçøðé@üñîçøðé.com>'));
        $this->assertTrue($validator->validate('<mail@cebe.cc>'));
        $this->assertTrue($validator->validate('test@example.com'));
        $this->assertTrue($validator->validate('John Smith <john.smith@example.com>'));
        $this->assertTrue($validator->validate('"Такое имя достаточно длинное, но оно все равно может пройти валидацию" <shortmail@example.com>'));
        $this->assertTrue($validator->validate('"FirstName LastName" <firstName.x.lastName.-nd@example.com>'));
        $this->assertFalse($validator->validate('John Smith <example.com>'));
        $this->assertFalse($validator->validate('Короткое имя <после-преобразования-в-idn-тут-будет-больше-чем-64-символа@пример.com>'));
        $this->assertFalse($validator->validate('Короткое имя <тест@это-доменное-имя.после-преобразования-в-idn.будет-содержать-больше-254-символов.бла-бла-бла-бла-бла-бла-бла-бла.бла-бла-бла-бла-бла-бла.бла-бла-бла-бла-бла-бла.бла-бла-бла-бла-бла-бла.com>'));
    }

    public function testValidateValueMx(): void
    {
        $validator = new EmailValidator();

        $validator->checkDNS = true;
        $this->assertTrue($validator->validate('5011@gmail.com'));

        $validator->checkDNS = false;
        $this->assertTrue($validator->validate('test@nonexistingsubdomain.example.com'));
        $validator->checkDNS = true;
        $this->assertFalse($validator->validate('test@nonexistingsubdomain.example.com'));

        $validator->checkDNS = true;
        $validator->allowName = true;
        $emails = [
            'ipetrov@gmail.com',
            'Ivan Petrov <ipetrov@gmail.com>',
        ];
        foreach ($emails as $email) {
            $this->assertTrue(
                $validator->validate($email),
                "Email: '$email' failed to validate(checkDNS=true, allowName=true)",
            );
        }
    }

    public function testValidateAttribute(): void
    {
        $validator = new EmailValidator();
        $model = new FakedValidationModel();
        $model->attr_email = '5011@gmail.com';
        $validator->validateAttribute($model, 'attr_email');
        $this->assertFalse($model->hasErrors('attr_email'));
    }

    /**
     * Test malicious email addresses that can be used to exploit SwiftMailer vulnerability CVE-2016-10074 while IDN is
     * disabled.
     *
     * @see https://legalhackers.com/advisories/SwiftMailer-Exploit-Remote-Code-Exec-CVE-2016-10074-Vuln.html
     *
     * @dataProvider \yiiunit\framework\validators\providers\EmailValidatorProvider::malformedAddressesProvider
     */
    public function testMalformedAddressesIdnDisabled(string $value): void
    {
        $validator = new EmailValidator();

        $validator->enableIDN = false;

        $this->assertFalse($validator->validate($value));
    }

    /**
     * Test malicious email addresses that can be used to exploit SwiftMailer vulnerability CVE-2016-10074 while IDN is
     * enabled.
     *
     * @see https://legalhackers.com/advisories/SwiftMailer-Exploit-Remote-Code-Exec-CVE-2016-10074-Vuln.html
     *
     * @dataProvider \yiiunit\framework\validators\providers\EmailValidatorProvider::malformedAddressesProvider
     */
    public function testMalformedAddressesIdnEnabled(string $value): void
    {
        if (!function_exists('idn_to_ascii')) {
            $this->markTestSkipped('Intl extension required');
        }

        $val = new EmailValidator();

        $val->enableIDN = true;

        $this->assertFalse($val->validate($value));
    }

    public function testClientValidateAttribute(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new EmailValidator();

        $modelValidator->attrA = 'test@example.com';

        $this->assertSame(
            'yii.validation.email(value, messages, {"pattern":' . $validator->pattern . ',"fullPattern":' .
            $validator->fullPattern . ',"allowName":false,"message":"attrA is not a valid email address.",' .
            '"enableIDN":false,"skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->getView()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');
        $clientOptions['fullPattern'] = (string) ($clientOptions['fullPattern'] ?? '');

        $this->assertSame(
            [
                'pattern' => $validator->pattern,
                'fullPattern' => $validator->fullPattern,
                'allowName' => false,
                'message' => 'attrA is not a valid email address.',
                'enableIDN' => false,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('invalid-email', $errorMessage);

        $this->assertSame(
            'the input value is not a valid email address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithEnableIDN(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new EmailValidator(['enableIDN' => true]);

        $this->assertSame(
            'yii.validation.email(value, messages, {"pattern":' . $validator->pattern . ',"fullPattern":' .
            $validator->fullPattern . ',"allowName":false,"message":"attrA is not a valid email address.",' .
            '"enableIDN":true,"skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->getView()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');
        $clientOptions['fullPattern'] = (string) ($clientOptions['fullPattern'] ?? '');

        $this->assertSame(
            [
                'pattern' => $validator->pattern,
                'fullPattern' => $validator->fullPattern,
                'allowName' => false,
                'message' => 'attrA is not a valid email address.',
                'enableIDN' => true,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('invalid-email', $errorMessage);

        $this->assertSame(
            'the input value is not a valid email address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testDnsCheckHandlesErrorException(): void
    {
        $this->stubDnsGetRecordThrowsException(true);

        $validator = new EmailValidator();

        $validator->checkDNS = true;

        $this->assertFalse(
            $validator->validate('test@example.com'),
            'Should return false when dns_get_record throws ErrorException'
        );
    }

    public function testThrowExceptionWhenIdnEnabledWithoutIntlExtension(): void
    {
        $this->stubIdnToAsciiExists(false);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('In order to use IDN validation intl extension must be installed and enabled.');

        new EmailValidator(['enableIDN' => true]);
    }
}

namespace yii\validators;

use yii\base\ErrorException;
use yiiunit\framework\validators\EmailValidatorTest;

function dns_get_record(string $hostname, int $type = DNS_ANY): array|false
{
    if (EmailValidatorTest::shouldDnsThrowExceptionStub()) {
        throw new ErrorException('DNS query failed,');
    }

    return \dns_get_record($hostname, $type);
}

function function_exists(string $name): bool
{
    $testValue = EmailValidatorTest::getIdnToAsciiExistsStub();

    if ($testValue !== null && $name === 'idn_to_ascii') {
        return $testValue;
    }

    return \function_exists($name);
}
