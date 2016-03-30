<?php
namespace yiiunit\framework\validators;

use yii\validators\EmailValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class EmailValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testValidateValue()
    {
        $validator = new EmailValidator();

        $this->assertTrue($validator->validate('sam@rmcreative.ru'));
        $this->assertTrue($validator->validate('5011@gmail.com'));
        $this->assertTrue($validator->validate('Abc.123@example.com'));
        $this->assertTrue($validator->validate('user+mailbox/department=shipping@example.com'));
        $this->assertTrue($validator->validate('!#$%&\'*+-/=?^_`.{|}~@example.com'));
        $this->assertFalse($validator->validate('rmcreative.ru'));
        $this->assertFalse($validator->validate('Carsten Brandt <mail@cebe.cc>'));
        $this->assertFalse($validator->validate('"Carsten Brandt" <mail@cebe.cc>'));
        $this->assertFalse($validator->validate('<mail@cebe.cc>'));
        $this->assertFalse($validator->validate('info@örtliches.de'));
        $this->assertFalse($validator->validate('sam@рмкреатиф.ru'));

        $validator->allowName = true;

        $this->assertTrue($validator->validate('sam@rmcreative.ru'));
        $this->assertTrue($validator->validate('5011@gmail.com'));
        $this->assertFalse($validator->validate('rmcreative.ru'));
        $this->assertTrue($validator->validate('Carsten Brandt <mail@cebe.cc>'));
        $this->assertTrue($validator->validate('"Carsten Brandt" <mail@cebe.cc>'));
        $this->assertTrue($validator->validate('<mail@cebe.cc>'));
        $this->assertFalse($validator->validate('info@örtliches.de'));
        $this->assertFalse($validator->validate('sam@рмкреатиф.ru'));
        $this->assertFalse($validator->validate('Informtation info@oertliches.de'));
        $this->assertTrue($validator->validate('test@example.com'));
        $this->assertTrue($validator->validate('John Smith <john.smith@example.com>'));
        $this->assertTrue($validator->validate('"This name is longer than 64 characters. Blah blah blah blah blah" <shortmail@example.com>'));
        $this->assertFalse($validator->validate('John Smith <example.com>'));
        $this->assertFalse($validator->validate('Short Name <localPartMoreThan64Characters-blah-blah-blah-blah-blah-blah-blah-blah@example.com>'));
        $this->assertFalse($validator->validate('Short Name <domainNameIsMoreThan254Characters@example-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah.com>'));
    }

    public function testValidateValueIdn()
    {
        if (!function_exists('idn_to_ascii')) {
            $this->markTestSkipped('Intl extension required');

            return;
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
        $this->assertTrue($validator->validate('<mail@cebe.cc>'));
        $this->assertTrue($validator->validate('test@example.com'));
        $this->assertTrue($validator->validate('John Smith <john.smith@example.com>'));
        $this->assertTrue($validator->validate('"Такое имя достаточно длинное, но оно все равно может пройти валидацию" <shortmail@example.com>'));
        $this->assertFalse($validator->validate('John Smith <example.com>'));
        $this->assertFalse($validator->validate('Короткое имя <после-преобразования-в-idn-тут-будет-больше-чем-64-символа@пример.com>'));
        $this->assertFalse($validator->validate('Короткое имя <тест@это-доменное-имя.после-преобразования-в-idn.будет-содержать-больше-254-символов.бла-бла-бла-бла-бла-бла-бла-бла.бла-бла-бла-бла-бла-бла.бла-бла-бла-бла-бла-бла.бла-бла-бла-бла-бла-бла.com>'));
    }

    public function testValidateValueMx()
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
        foreach($emails as $email) {
            $this->assertTrue($validator->validate($email),"Email: '$email' failed to validate(checkDNS=true, allowName=true)");
        }
    }

    public function testValidateAttribute()
    {
        $val = new EmailValidator();
        $model = new FakedValidationModel();
        $model->attr_email = '5011@gmail.com';
        $val->validateAttribute($model, 'attr_email');
        $this->assertFalse($model->hasErrors('attr_email'));
    }
}
