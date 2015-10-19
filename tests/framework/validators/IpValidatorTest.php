<?php
namespace yiiunit\framework\validators;

use yii\validators\IpValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class IpValidatorTest extends TestCase
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
        new IpValidator(['ipv4' => false, 'ipv6' => false]);
    }

    public function testAssureMessageSetOnInit()
    {
        $val = new IpValidator([
            'ips' => [
                'allow' => '10.0.0.1',
                'deny' => '10.0.0.2'
            ]
        ]);
        $this->assertTrue(is_array($val->ips));
        $this->assertTrue(is_array($val->ips['allow']));
        $this->assertTrue(is_array($val->ips['deny']));
    }

    public function testValidateIpsAllowDenyOrder()
    {
        $validator = new IpValidator([
            'ips' => [
                'deny' => ['192.168.0.0/16'],
                'allow' => ['192.168.1.0/24'],
            ]
        ]);

        $this->assertTrue($validator->validate('192.168.1.2'));
        $this->assertFalse($validator->validate('192.168.2.2'));
        $this->assertTrue($validator->validate('192.167.2.2'));
    }

    public function testValidateIpsDenyAllowOrder()
    {
        $validator = new IpValidator([
            'ips' => [
                'allow' => ['192.168.1.0/24', '172.20.10.2'],
                'deny' => ['192.168.0.0/16'],
            ]
        ]);

        $this->assertTrue($validator->validate('172.20.10.2'));
        $this->assertFalse($validator->validate('192.168.1.2'));
        $this->assertFalse($validator->validate('192.168.2.2'));
        $this->assertFalse($validator->validate('192.167.2.2'));
    }

    public function testValidateValueNotAnIP()
    {
        $validator = new IpValidator();

        $this->assertFalse($validator->validate('not.an.ip'));
        $this->assertFalse($validator->validate(['what an array', '??']));
        $this->assertFalse($validator->validate(123456));
        $this->assertFalse($validator->validate(false));
        $this->assertFalse($validator->validate(true));
    }

    public function testValidateValueIPv4()
    {
        $validator = new IpValidator();

        $this->assertTrue($validator->validate('192.168.10.11'));
        $this->assertTrue($validator->validate('192.168.005.001'));
        $this->assertFalse($validator->validate('192.168.5.321'));
        $this->assertFalse($validator->validate('!192.168.5.32'));
        $this->assertFalse($validator->validate('192.168.5.32/11'));

        $validator->ipv4 = false;
        $this->assertFalse($validator->validate('192.168.10.11'));

        $validator->ipv4 = true;
        $validator->subnet = null;

        $this->assertTrue($validator->validate('192.168.5.32/11'));
        $this->assertTrue($validator->validate('192.168.5.32/32'));
        $this->assertTrue($validator->validate('0.0.0.0/0'));
        $this->assertFalse($validator->validate('192.168.5.32/33'));
        $this->assertFalse($validator->validate('192.168.5.32/33'));
        $this->assertFalse($validator->validate('192.168.5.32/af'));
        $this->assertFalse($validator->validate('192.168.5.32/11/12'));

        $validator->subnet = true;
        $this->assertTrue($validator->validate('10.0.0.1/24'));
        $this->assertTrue($validator->validate('10.0.0.1/0'));
        $this->assertFalse($validator->validate('10.0.0.1'));

        $validator->negationChar = true;

        $this->assertTrue($validator->validate('!192.168.5.32/32'));
        $this->assertFalse($validator->validate('!!192.168.5.32/32'));
    }


    public function testValidateValueIPv6()
    {
        if (!defined('AF_INET6')) {
            $this->markTestSkipped('The system does not supports IPv6.');
        }

        $validator = new IpValidator();

        $this->assertTrue($validator->validate('2008:fa::1'));
        $this->assertTrue($validator->validate('2008:00fa::0001'));
        $this->assertFalse($validator->validate('2008:fz::0'));
        $this->assertFalse($validator->validate('2008:fa::0::1'));
        $this->assertFalse($validator->validate('!2008:fa::0::1'));
        $this->assertFalse($validator->validate('2008:fa::0:1/64'));

        $validator->ipv4 = false;
        $this->assertTrue($validator->validate('2008:fa::1'));

        $validator->ipv6 = false;
        $this->assertFalse($validator->validate('2008:fa::1'));

        $validator->ipv6 = true;
        $validator->subnet = null;

        $this->assertTrue($validator->validate('2008:fa::0:1/64'));
        $this->assertTrue($validator->validate('2008:fa::0:1/128'));
        $this->assertTrue($validator->validate('2008:fa::0:1/0'));
        $this->assertFalse($validator->validate('!2008:fa::0:1/0'));
        $this->assertFalse($validator->validate('2008:fz::0/129'));

        $validator->subnet = true;
        $this->assertTrue($validator->validate('2008:db0::1/64'));
        $this->assertFalse($validator->validate('2008:db0::1'));

        $validator->negationChar = true;
        $this->assertTrue($validator->validate('!2008:fa::0:1/64'));
        $this->assertFalse($validator->validate('!!2008:fa::0:1/64'));
    }

    public function testValidateValueIPvBoth()
    {
        if (!defined('AF_INET6')) {
            $this->markTestSkipped('The system does not supports IPv6.');
        }

        $validator = new IpValidator();

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
        $validator->subnet = null;

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

        $validator->subnet = true;
        $this->assertTrue($validator->validate('10.0.0.1/24'));
        $this->assertTrue($validator->validate('10.0.0.1/0'));
        $this->assertTrue($validator->validate('2008:db0::1/64'));
        $this->assertFalse($validator->validate('2008:db0::1'));
        $this->assertFalse($validator->validate('10.0.0.1'));

        $validator->negationChar = true;

        $this->assertTrue($validator->validate('!192.168.5.32/32'));
        $this->assertTrue($validator->validate('!2008:fa::0:1/64'));
        $this->assertFalse($validator->validate('!!192.168.5.32/32'));
        $this->assertFalse($validator->validate('!!2008:fa::0:1/64'));
    }

    public function testValidateRangeIPv4()
    {
        $validator = new IpValidator([
            'ips' => [
                'allow' => '10.0.1.0/24',
            ]
        ]);
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('192.5.1.1'));

        $validator->ips['allow'] = ['10.0.1.0/24', '127.0.0.1'];
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('10.0.3.2'));

        $validator->ips['deny'] = ['10.0.0.0/8'];
        $this->assertFalse($validator->validate('10.0.1.2'));
        $this->assertTrue($validator->validate('127.0.0.1'));

        $validator->subnet = null;
        // Change order to deny, allow
        $validator->ips = array_reverse($validator->ips);
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertTrue($validator->validate('127.0.0.1'));
        $this->assertTrue($validator->validate('10.0.1.28/28'));
        $this->assertFalse($validator->validate('10.2.2.2'));
        $this->assertFalse($validator->validate('10.0.1.1/22'));
    }

    public function testValidateRangeIPv6()
    {
        if (!defined('AF_INET6')) {
            $this->markTestSkipped('The system does not supports IPv6.');
        }

        $validator = new IpValidator([
            'ips' => [
                'allow' => '2001:db0:1:1::/64',
            ]
        ]);
        $this->assertTrue($validator->validate('2001:db0:1:1::6'));
        $this->assertFalse($validator->validate('2001:db0:1:2::7'));

        $validator->ips['allow'] = ['2001:db0:1:2::/64'];
        $this->assertTrue($validator->validate('2001:db0:1:2::7'));

        $validator->ips['deny'] = ['2001:db0::/32'];
        $this->assertFalse($validator->validate('2001:db0:1:2::7'));

        $validator->subnet = null;
        // Change order to deny, allow
        $validator->ips = array_reverse($validator->ips);
        $this->assertTrue($validator->validate('2001:db0:1:2::7'));
    }

    public function testValidateRangeIPvBoth()
    {
        if (!defined('AF_INET6')) {
            $this->markTestSkipped('The system does not supports IPv6.');
        }

        $validator = new IpValidator([
            'ips' => [
                'allow' => '10.0.1.0/24',
            ]
        ]);
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('192.5.1.1'));
        $this->assertFalse($validator->validate('2001:db0:1:2::7'));

        $validator->ips['allow'] = ['10.0.1.0/24', '2001:db0:1:2::/64', '127.0.0.1'];
        $this->assertTrue($validator->validate('2001:db0:1:2::7'));
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('10.0.3.2'));

        $validator->ips['deny'] = ['10.0.0.0/8', '2001:db0::/32'];
        $this->assertFalse($validator->validate('2001:db0:1:2::7'));
        $this->assertFalse($validator->validate('10.0.1.2'));
        $this->assertTrue($validator->validate('127.0.0.1'));

        $validator->subnet = null;
        // Change order to deny, allow
        $validator->ips = array_reverse($validator->ips);
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertTrue($validator->validate('2001:db0:1:2::7'));
        $this->assertTrue($validator->validate('127.0.0.1'));
        $this->assertTrue($validator->validate('10.0.1.28/28'));
        $this->assertFalse($validator->validate('10.2.2.2'));
        $this->assertFalse($validator->validate('10.0.1.1/22'));
    }

    public function testValidateAttributeIPv4()
    {
        $validator = new IpValidator();
        $model = new FakedValidationModel();

        $validator->subnet = null;

        $model->attr_ip = '8.8.8.8';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('8.8.8.8', $model->attr_ip);

        $validator->subnet = false;

        $model->attr_ip = '8.8.8.8';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('8.8.8.8', $model->attr_ip);

        $model->attr_ip = '8.8.8.8/24';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertTrue($model->hasErrors('attr_ip'));
        $this->assertEquals('attr_ip must not be a subnet.', $model->getFirstError('attr_ip'));
        $model->clearErrors();

        $validator->subnet = null;
        $validator->normalize = true;

        $model->attr_ip = '8.8.8.8';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('8.8.8.8/32', $model->attr_ip);
    }


    public function testValidateAttributeIPv6()
    {
        if (!defined('AF_INET6')) {
            $this->markTestSkipped('The system does not supports IPv6.');
        }

        $validator = new IpValidator();
        $model = new FakedValidationModel();

        $validator->subnet = null;

        $model->attr_ip = '2001:db0:1:2::1';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('2001:db0:1:2::1', $model->attr_ip);

        $validator->subnet = false;

        $model->attr_ip = '2001:db0:1:2::7';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('2001:db0:1:2::7', $model->attr_ip);

        $model->attr_ip = '2001:db0:1:2::7/64';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertTrue($model->hasErrors('attr_ip'));
        $this->assertEquals('attr_ip must not be a subnet.', $model->getFirstError('attr_ip'));
        $model->clearErrors();

        $validator->subnet = null;
        $validator->normalize = true;

        $model->attr_ip = 'fa01::1';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('fa01::1/128', $model->attr_ip);

        $model->attr_ip = 'fa01::1/64';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('fa01::1/64', $model->attr_ip);

        $validator->expandIPv6 = true;

        $model->attr_ip = 'fa01::1/64';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertFalse($model->hasErrors('attr_ip'));
        $this->assertEquals('fa01:0000:0000:0000:0000:0000:0000:0001/64', $model->attr_ip);

        $model->attr_ip = 'fa01::2/614';
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertTrue($model->hasErrors('attr_ip'));
        $this->assertEquals('fa01::2/614', $model->attr_ip);
        $this->assertEquals('attr_ip contains wrong subnet mask.', $model->getFirstError('attr_ip'));
    }

    public function testNegationChar()
    {
        $validator = new IpValidator([
            'negationChar' => '(not!)'
        ]);
        $this->assertTrue($validator->validate('(not!)192.168.5.32'));
        $this->assertFalse($validator->validate('!192.168.5.32'));
    }
}
