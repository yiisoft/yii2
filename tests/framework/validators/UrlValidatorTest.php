<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\validators\UrlValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class UrlValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testValidateValue()
    {
        $val = new UrlValidator();
        $this->assertFalse($val->validate('google.de'));
        $this->assertTrue($val->validate('http://google.de'));
        $this->assertTrue($val->validate('https://google.de'));
        $this->assertFalse($val->validate('htp://yiiframework.com'));
        $this->assertTrue($val->validate('https://www.google.de/search?q=yii+framework&ie=utf-8&oe=utf-8'
                                        . '&rls=org.mozilla:de:official&client=firefox-a&gws_rd=cr'));
        $this->assertFalse($val->validate('ftp://ftp.ruhr-uni-bochum.de/'));
        $this->assertFalse($val->validate('http://invalid,domain'));
        $this->assertFalse($val->validate('http://example.com,'));
        $this->assertFalse($val->validate('http://example.com*12'));
        $this->assertTrue($val->validate('http://example.com/*12'));
        $this->assertTrue($val->validate('http://example.com/?test'));
        $this->assertTrue($val->validate('http://example.com/#test'));
        $this->assertTrue($val->validate('http://example.com:80/#test'));
        $this->assertTrue($val->validate('http://example.com:65535/#test'));
        $this->assertTrue($val->validate('http://example.com:81/?good'));
        $this->assertTrue($val->validate('http://example.com?test'));
        $this->assertTrue($val->validate('http://example.com#test'));
        $this->assertTrue($val->validate('http://example.com:81#test'));
        $this->assertTrue($val->validate('http://example.com:81?good'));
        $this->assertFalse($val->validate('http://example.com,?test'));
        $this->assertFalse($val->validate('http://example.com:?test'));
        $this->assertFalse($val->validate('http://example.com:test'));
        $this->assertFalse($val->validate('http://example.com:123456/test'));

        $this->assertFalse($val->validate('http://äüö?=!"§$%&/()=}][{³²€.edu'));
    }

    public function testValidateValueWithDefaultScheme()
    {
        $val = new UrlValidator(['defaultScheme' => 'https']);
        $this->assertTrue($val->validate('yiiframework.com'));
        $this->assertTrue($val->validate('http://yiiframework.com'));
    }

    public function testValidateValueWithoutScheme()
    {
        $val = new UrlValidator(['pattern' => '/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i']);
        $this->assertTrue($val->validate('yiiframework.com'));
    }

    public function testValidateWithCustomScheme()
    {
        $val = new UrlValidator([
            'validSchemes' => ['http', 'https', 'ftp', 'ftps'],
            'defaultScheme' => 'http',
        ]);
        $this->assertTrue($val->validate('ftp://ftp.ruhr-uni-bochum.de/'));
        $this->assertTrue($val->validate('google.de'));
        $this->assertTrue($val->validate('http://google.de'));
        $this->assertTrue($val->validate('https://google.de'));
        $this->assertFalse($val->validate('htp://yiiframework.com'));
        // relative urls not supported
        $this->assertFalse($val->validate('//yiiframework.com'));
    }

    public function testValidateWithIdn()
    {
        if (!function_exists('idn_to_ascii')) {
            $this->markTestSkipped('intl package required');

            return;
        }
        $val = new UrlValidator([
            'enableIDN' => true,
        ]);
        $this->assertTrue($val->validate('http://äüößìà.de'));
        // converted via http://mct.verisign-grs.com/convertServlet
        $this->assertTrue($val->validate('http://xn--zcack7ayc9a.de'));
    }

    public function testValidateLength()
    {
        $url = 'http://' . str_pad('base', 2000, 'url') . '.de';
        $val = new UrlValidator();
        $this->assertFalse($val->validate($url));
    }

    public function testValidateAttributeAndError()
    {
        $obj = new FakedValidationModel();
        $obj->attr_url = 'http://google.de';
        $val = new UrlValidator();
        $val->validateAttribute($obj, 'attr_url');
        $this->assertFalse($obj->hasErrors('attr_url'));
        $this->assertSame('http://google.de', $obj->attr_url);
        $obj = new FakedValidationModel();
        $val->defaultScheme = 'http';
        $obj->attr_url = 'google.de';
        $val->validateAttribute($obj, 'attr_url');
        $this->assertFalse($obj->hasErrors('attr_url'));
        $this->assertNotFalse(stripos($obj->attr_url, 'http'));
        $obj = new FakedValidationModel();
        $obj->attr_url = 'gttp;/invalid string';
        $val->validateAttribute($obj, 'attr_url');
        $this->assertTrue($obj->hasErrors('attr_url'));
    }
}
