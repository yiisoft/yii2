<?php
namespace yiiunit\framework\validators;
use yiiunit\data\validators\models\FakedValidationModel;
use yii\validators\UrlValidator;
use yiiunit\TestCase;

/**
 * UrlValidatorTest
 */
class UrlValidatorTest extends TestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}

	public function testValidateValue()
	{
		$val = new UrlValidator;
		$this->assertFalse($val->validateValue('google.de'));
		$this->assertTrue($val->validateValue('http://google.de'));
		$this->assertTrue($val->validateValue('https://google.de'));
		$this->assertFalse($val->validateValue('htp://yiiframework.com'));
		$this->assertTrue($val->validateValue('https://www.google.de/search?q=yii+framework&ie=utf-8&oe=utf-8'
										.'&rls=org.mozilla:de:official&client=firefox-a&gws_rd=cr'));
		$this->assertFalse($val->validateValue('ftp://ftp.ruhr-uni-bochum.de/'));
		$this->assertFalse($val->validateValue('http://invalid,domain'));
		$this->assertFalse($val->validateValue('http://äüö?=!"§$%&/()=}][{³²€.edu'));
	}
	
	public function testValidateValueWithDefaultScheme()
	{
		$val = new UrlValidator(['defaultScheme' => 'https']);
		$this->assertTrue($val->validateValue('yiiframework.com'));
		$this->assertTrue($val->validateValue('http://yiiframework.com'));
	}

	public function testValidateValueWithoutScheme()
	{
		$val = new UrlValidator(['pattern' => '/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i']);
		$this->assertTrue($val->validateValue('yiiframework.com'));
	}
	
	public function testValidateWithCustomScheme()
	{
		$val = new UrlValidator([
			'validSchemes' => ['http', 'https', 'ftp', 'ftps'],
			'defaultScheme' => 'http',
		]);
		$this->assertTrue($val->validateValue('ftp://ftp.ruhr-uni-bochum.de/'));
		$this->assertTrue($val->validateValue('google.de'));
		$this->assertTrue($val->validateValue('http://google.de'));
		$this->assertTrue($val->validateValue('https://google.de'));
		$this->assertFalse($val->validateValue('htp://yiiframework.com'));
		// relative urls not supported
		$this->assertFalse($val->validateValue('//yiiframework.com'));
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
		$this->assertTrue($val->validateValue('http://äüößìà.de'));
		// converted via http://mct.verisign-grs.com/convertServlet
		$this->assertTrue($val->validateValue('http://xn--zcack7ayc9a.de'));
	}
	
	public function testValidateLength()
	{
		$url = 'http://' . str_pad('base', 2000, 'url') . '.de';
		$val = new UrlValidator;
		$this->assertFalse($val->validateValue($url));
	}
	
	public function testValidateAttributeAndError()
	{
		$obj = new FakedValidationModel;
		$obj->attr_url = 'http://google.de';
		$val = new UrlValidator;
		$val->validateAttribute($obj, 'attr_url');
		$this->assertFalse($obj->hasErrors('attr_url'));
		$this->assertSame('http://google.de', $obj->attr_url);
		$obj = new FakedValidationModel;
		$val->defaultScheme = 'http';
		$obj->attr_url = 'google.de';
		$val->validateAttribute($obj, 'attr_url');
		$this->assertFalse($obj->hasErrors('attr_url'));
		$this->assertTrue(stripos($obj->attr_url, 'http') !== false);
		$obj = new FakedValidationModel;
		$obj->attr_url = 'gttp;/invalid string';
		$val->validateAttribute($obj, 'attr_url');
		$this->assertTrue($obj->hasErrors('attr_url'));
	}
}
