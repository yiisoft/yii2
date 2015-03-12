<?php
namespace yiiunit\framework\validators;

use yii\validators\IpAddressValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class IpAddressValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testInitException()
    {
        $this->setExpectedException('yii\base\InvalidConfigException',
            'Both IPv4 and IPv6 checks can not be disabled at the same time');
        new IpAddressValidator(['ipv4' => false, 'ipv6' => false]);
    }

    public function testAssureMessageSetOnInit()
    {
        $val = new IpAddressValidator([
            'allowedRanges' => '10.0.0.1',
            'deniedRanges' => ['10.0.0.2']
        ]);
        $this->assertTrue(is_array($val->allowedRanges));
        $this->assertTrue(is_array($val->deniedRanges));
    }

    public function testValidateValue()
    {
        $validator = new IpAddressValidator();

        $this->assertTrue($validator->validate('192.168.10.11'));
        $this->assertTrue($validator->validate('2008:fa::1'));
        $this->assertTrue($validator->validate('2008:00fa::0001'));
        $this->assertTrue($validator->validate('192.168.005.001'));
        $this->assertFalse($validator->validate('192.168.5.321'));
        $this->assertFalse($validator->validate('!192.168.5.32'));
        $this->assertFalse($validator->validate('192.168.5.32/11'));
        $this->assertFalse($validator->validate('2008:fz::0'));
        $this->assertFalse($validator->validate('2008:fa::0::1'));
        $this->assertFalse($validator->validate('!2008:fa::0::1'));
        $this->assertFalse($validator->validate('2008:fa::0:1/64'));

        $validator->ipv4 = false;
        $this->assertFalse($validator->validate('192.168.10.11'));
        $this->assertTrue($validator->validate('2008:fa::1'));

        $validator->ipv6 = false;
        $validator->ipv4 = true;
        $this->assertTrue($validator->validate('192.168.10.11'));
        $this->assertFalse($validator->validate('2008:fa::1'));

        $validator->ipv6 = true;
        $validator->subnet = true;

        $this->assertTrue($validator->validate('192.168.5.32/11'));
        $this->assertTrue($validator->validate('192.168.5.32/32'));
        $this->assertTrue($validator->validate('0.0.0.0/0'));
        $this->assertTrue($validator->validate('2008:fa::0:1/64'));
        $this->assertTrue($validator->validate('2008:fa::0:1/128'));
        $this->assertTrue($validator->validate('2008:fa::0:1/0'));
        $this->assertFalse($validator->validate('!2008:fa::0:1/0'));
        $this->assertFalse($validator->validate('192.168.5.32/33'));
        $this->assertFalse($validator->validate('2008:fz::0/129'));
        $this->assertFalse($validator->validate('192.168.5.32/33'));
        $this->assertFalse($validator->validate('192.168.5.32/af'));
        $this->assertFalse($validator->validate('192.168.5.32/11/12'));

        $validator->exclude = true;

        $this->assertTrue($validator->validate('!192.168.5.32/32'));
        $this->assertTrue($validator->validate('!2008:fa::0:1/64'));
        $this->assertFalse($validator->validate('!!192.168.5.32/32'));
        $this->assertFalse($validator->validate('!!2008:fa::0:1/64'));

        $this->assertFalse($validator->validate('not.an.ip'));
        $this->assertFalse($validator->validate(['what an array', '??']));
        $this->assertFalse($validator->validate(123456));
        $this->assertFalse($validator->validate(false));
        $this->assertFalse($validator->validate(true));
    }

    public function testValidateRange()
    {
        $validator = new IpAddressValidator([
            'allowedRanges' => ['10.0.1.0/24'],
        ]);
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('192.5.1.1'));
        $this->assertFalse($validator->validate('2001:db0:1:2::7'));

        $validator->allowedRanges = ['10.0.1.0/24', '2001:db0:1:2::/64', '127.0.0.1'];
        $this->assertTrue($validator->validate('2001:db0:1:2::7'));
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('10.0.3.2'));

        $validator->deniedRanges = ['10.0.0.0/8', '2001:db0::/32'];
        $this->assertFalse($validator->validate('2001:db0:1:2::7'));
        $this->assertFalse($validator->validate('10.0.1.2'));
        $this->assertTrue($validator->validate('127.0.0.1'));

        $validator->rangesOrder = IpAddressValidator::RANGE_ORDER_ALLOWED_DENIED;
        $validator->subnet = true;
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertTrue($validator->validate('2001:db0:1:2::7'));
        $this->assertTrue($validator->validate('127.0.0.1'));
        $this->assertTrue($validator->validate('10.0.1.28/28'));
        $this->assertFalse($validator->validate('10.2.2.2'));
        $this->assertTrue($validator->validate('10.0.1.1/22')); // bad test
    }

    public function testValidateAttribute()
    {
        $validator = new IpAddressValidator();
        $model = new FakedValidationModel();

        $model->attr_ip = '8.8.8.8';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('8.8.8.8', $model->attr_ip);

        $validator->normalize = true;
        $validator->subnet = true;

        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('8.8.8.8/32', $model->attr_ip);

        $model->attr_ip = 'fa01::1';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('fa01::1/128', $model->attr_ip);

        $model->attr_ip = 'fa01::1/64';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('fa01::1/64', $model->attr_ip);

        $validator->expandV6 = true;

        $model->attr_ip = 'fa01::1/64';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('fa01:0000:0000:0000:0000:0000:0000:0001/64', $model->attr_ip);

        $model->attr_ip = 'fa01::2/614';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertTrue($model->hasErrors('attr_ip'));
        $this->assertEquals('fa01::2/614', $model->attr_ip);
        $this->assertEquals('attr_ip contains wrong subnet mask', $model->getFirstError('attr_ip'));
    }
}
