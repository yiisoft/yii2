<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function emailProvider() {
        return [
            // valid, requiresIDN, hasName, email
            [false, false, false, 'ex..ample@example.com'],
            [false, false, false, 'Information info@oertliches.de'],
            [false, false, false, 'rmcreative.ru'],
            [false, false, false, ['developer@yiiframework.com']],
            [false, false, true, 'John Smith <example.com>'],
            [false, false, true, 'Short Name <domainNameIsMoreThan254Characters@example-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah-blah.com>'],
            [false, false, true, 'Short Name <localPartMoreThan64Characters-blah-blah-blah-blah-blah-blah-blah-blah@example.com>'],
            [true, false, false, '!#$%&\'*+-/=?^_`.{|}~@example.com'],
            [true, false, false, '5011@gmail.com'],
            [true, false, false, 'Abc.123@example.com'],
            [true, false, false, 'sam@rmcreative.ru'],
            [true, false, false, 'test-@example.com'],
            [true, false, false, 'test@example.com'],
            [true, false, false, 'user+mailbox/department=shipping@example.com'],
            [true, false, true, '"Carsten Brandt" <mail@cebe.cc>'],
            [true, false, true, '"This name is longer than 64 characters. Blah blah blah blah blah" <shortmail@example.com>'],
            [true, false, true, '"Такое имя достаточно длинное, но оно все равно может пройти валидацию" <shortmail@example.com>'],
            [true, false, true, '<mail@cebe.cc>'],
            [true, false, true, 'Carsten Brandt <mail@cebe.cc>'],
            [true, false, true, 'example@xn--zcack7ayc9a.de'],
            [true, false, true, 'John Smith <john.smith@example.com>'],
            [true, true, false, 'example@äüößìà.de'],
            [true, true, false, 'info@örtliches.de'],
            [true, true, false, 'sam@рмкреатиф.ru'],
            [true, true, false, 'üñîçøðé@üñîçøðé.com'],
            [true, true, true, 'Information <info@örtliches.de>'],
            [true, true, true, 'üñîçøðé 日本国 <üñîçøðé@üñîçøðé.com>'],
            [true, true, false, 'после-преобразования-в-idn-тут-будет-больше-чем-64-символа@пример.com'],
            [true, true, true, 'Короткое имя <после-преобразования-в-idn-тут-будет-больше-чем-64-символа@пример.com>'],
            [false, true, true, 'Короткое имя <тест@это-доменное-имя.после-преобразования-в-idn.будет-содержать-больше-254-символов.бла-бла-бла-бла-бла-бла-бла-бла.бла-бла-бла-бла-бла-бла.бла-бла-бла-бла-бла-бла.бла-бла-бла-бла-бла-бла.com>']
        ];
    }

    /**
     * Each email is tested multiple times with different configurations
     * @dataProvider emailProvider
     * @param $email
     * @param $valid
     * @param $requiresIdn
     * @param $hasName
     */
    public function testValidateValue($valid, $requiresIdn, $hasName, $email)
    {
        $validator = new EmailValidator();

        /**
         * Validation should fail if the email requires IDN or contains a name or if it's invalid.
         */
        $result = $validator->validate($email, $error);
        $this->assertEquals($valid && !$requiresIdn && !$hasName, $result, $error);

        $validator->allowName = true;

        /**
         * Validation should fail if the email requires IDN or is invalid.
         */

        $result = $validator->validate($email, $error);
        $this->assertEquals($valid && !$requiresIdn, $result, $error);


        if ($requiresIdn && !function_exists('idn_to_ascii')) {
            $this->markTestSkipped('Intl extension required');
            return;
        }

        $validator->enableIDN = true;

        /**
         * Validation should fail if the email is invalid.
         */
        $result = $validator->validate($email, $error);
        $this->assertEquals($valid, $result, $error);
        $validator->allowName = false;

        /**
         * Validation should fail if the email is invalid or contains a name.
         */
        $result = $validator->validate($email, $error);
        $this->assertEquals($valid && !$hasName, $result, $error);
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
        foreach ($emails as $email) {
            $this->assertTrue($validator->validate($email), "Email: '$email' failed to validate(checkDNS=true, allowName=true)");
        }
    }

    public function testValidateAttribute()
    {
        $validator = new EmailValidator();
        $model = new FakedValidationModel();
        $model->attr_email = '5011@gmail.com';
        $validator->validateAttribute($model, 'attr_email');
        $this->assertFalse($model->hasErrors('attr_email'));
    }

    public function malformedAddressesProvider()
    {
        return [
            // this is the demo email used in the proof of concept of the exploit
            ['"attacker\" -oQ/tmp/ -X/var/www/cache/phpcode.php "@email.com'],
            // trying more adresses
            ['"Attacker -Param2 -Param3"@test.com'],
            ['\'Attacker -Param2 -Param3\'@test.com'],
            ['"Attacker \" -Param2 -Param3"@test.com'],
            ["'Attacker \\' -Param2 -Param3'@test.com"],
            ['"attacker\" -oQ/tmp/ -X/var/www/cache/phpcode.php "@email.com'],
            // and even more variants
            ['"attacker\"\ -oQ/tmp/\ -X/var/www/cache/phpcode.php"@email.com'],
            ["\"attacker\\\"\0-oQ/tmp/\0-X/var/www/cache/phpcode.php\"@email.com"],
            ['"attacker@cebe.cc\"-Xbeep"@email.com'],

            ["'attacker\\' -oQ/tmp/ -X/var/www/cache/phpcode.php'@email.com"],
            ["'attacker\\\\' -oQ/tmp/ -X/var/www/cache/phpcode.php'@email.com"],
            ["'attacker\\\\'\\ -oQ/tmp/ -X/var/www/cache/phpcode.php'@email.com"],
            ["'attacker\\';touch /tmp/hackme'@email.com"],
            ["'attacker\\\\';touch /tmp/hackme'@email.com"],
            ["'attacker\\';touch/tmp/hackme'@email.com"],
            ["'attacker\\\\';touch/tmp/hackme'@email.com"],
            ['"attacker\" -oQ/tmp/ -X/var/www/cache/phpcode.php "@email.com'],
        ];
    }

    /**
     * Test malicious email addresses that can be used to exploit SwiftMailer vulnerability CVE-2016-10074 while IDN is disabled.
     * @see https://legalhackers.com/advisories/SwiftMailer-Exploit-Remote-Code-Exec-CVE-2016-10074-Vuln.html
     *
     * @dataProvider malformedAddressesProvider
     * @param string $value
     */
    public function testMalformedAddressesIdnDisabled($value)
    {
        $validator = new EmailValidator();
        $validator->enableIDN = false;
        $this->assertFalse($validator->validate($value));
    }

    /**
     * Test malicious email addresses that can be used to exploit SwiftMailer vulnerability CVE-2016-10074 while IDN is enabled.
     * @see https://legalhackers.com/advisories/SwiftMailer-Exploit-Remote-Code-Exec-CVE-2016-10074-Vuln.html
     *
     * @dataProvider malformedAddressesProvider
     * @param string $value
     */
    public function testMalformedAddressesIdnEnabled($value)
    {
        if (!function_exists('idn_to_ascii')) {
            $this->markTestSkipped('Intl extension required');
            return;
        }

        $val = new EmailValidator();
        $val->enableIDN = true;
        $this->assertFalse($val->validate($value));
    }
}
