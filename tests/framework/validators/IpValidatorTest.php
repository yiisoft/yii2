<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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
        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testInitException()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Both IPv4 and IPv6 checks can not be disabled at the same time');
        new IpValidator(['ipv4' => false, 'ipv6' => false]);
    }

    public function provideRangesForSubstitution()
    {
        return [
            ['10.0.0.1', ['10.0.0.1']],
            [['192.168.0.32', 'fa::/32', 'any'], ['192.168.0.32', 'fa::/32', '0.0.0.0/0', '::/0']],
            [['10.0.0.1', '!private'], ['10.0.0.1', '!10.0.0.0/8', '!172.16.0.0/12', '!192.168.0.0/16', '!fd00::/8']],
            [['private', '!system'], ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', 'fd00::/8', '!224.0.0.0/4', '!ff00::/8', '!169.254.0.0/16', '!fe80::/10', '!127.0.0.0/8', '!::1', '!192.0.2.0/24', '!198.51.100.0/24', '!203.0.113.0/24', '!2001:db8::/32']],
        ];
    }

    /**
     * @dataProvider provideRangesForSubstitution
     */
    public function testRangesSubstitution($range, $expectedRange)
    {
        $validator = new IpValidator(['ranges' => $range]);
        $this->assertEquals($expectedRange, $validator->ranges);
    }


    public function testValidateOrder()
    {
        $validator = new IpValidator([
            'ranges' => ['10.0.0.1', '!10.0.0.0/8', '!babe::/8', 'any'],
        ]);

        $this->assertTrue($validator->validate('10.0.0.1'));
        $this->assertFalse($validator->validate('10.0.0.2'));
        $this->assertTrue($validator->validate('192.168.5.101'));
        $this->assertTrue($validator->validate('cafe::babe'));
        $this->assertFalse($validator->validate('babe::cafe'));
    }

    public function provideBadIps()
    {
        return [['not.an.ip'], [['what an array', '??']], [123456], [true], [false], ['bad:forSure']];
    }

    /**
     * @dataProvider provideBadIps
     */
    public function testValidateValueNotAnIP($badIp)
    {
        $validator = new IpValidator();

        $this->assertFalse($validator->validate($badIp));
    }

    /**
     * @dataProvider provideBadIps
     */
    public function testValidateModelAttributeNotAnIP($badIp)
    {
        $validator = new IpValidator();
        $model = new FakedValidationModel();

        $model->attr_ip = $badIp;
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertEquals('attr_ip must be a valid IP address.', $model->getFirstError('attr_ip'));
        $model->clearErrors();


        $validator->ipv4 = false;

        $model->attr_ip = $badIp;
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertEquals('attr_ip must be a valid IP address.', $model->getFirstError('attr_ip'));
        $model->clearErrors();


        $validator->ipv4 = true;
        $validator->ipv6 = false;

        $model->attr_ip = $badIp;
        $validator->validateAttribute($model, 'attr_ip');
        $this->assertEquals('attr_ip must be a valid IP address.', $model->getFirstError('attr_ip'));
        $model->clearErrors();
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

        $validator->negation = true;
        $this->assertTrue($validator->validate('!192.168.5.32/32'));
        $this->assertFalse($validator->validate('!!192.168.5.32/32'));
    }


    public function testValidateValueIPv6()
    {
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

        $validator->negation = true;
        $this->assertTrue($validator->validate('!2008:fa::0:1/64'));
        $this->assertFalse($validator->validate('!!2008:fa::0:1/64'));
    }

    public function testValidateValueIPvBoth()
    {
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

        $validator->negation = true;

        $this->assertTrue($validator->validate('!192.168.5.32/32'));
        $this->assertTrue($validator->validate('!2008:fa::0:1/64'));
        $this->assertFalse($validator->validate('!!192.168.5.32/32'));
        $this->assertFalse($validator->validate('!!2008:fa::0:1/64'));
    }

    public function testValidateRangeIPv4()
    {
        $validator = new IpValidator([
            'ranges' => ['10.0.1.0/24'],
        ]);
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('192.5.1.1'));

        $validator->ranges = ['10.0.1.0/24'];
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('10.0.3.2'));

        $validator->ranges = ['!10.0.1.0/24', '10.0.0.0/8', 'localhost'];
        $this->assertFalse($validator->validate('10.0.1.2'));
        $this->assertTrue($validator->validate('127.0.0.1'));

        $validator->subnet = null;
        $validator->ranges = ['10.0.1.0/24', '!10.0.0.0/8', 'localhost'];
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertTrue($validator->validate('127.0.0.1'));
        $this->assertTrue($validator->validate('10.0.1.28/28'));
        $this->assertFalse($validator->validate('10.2.2.2'));
        $this->assertFalse($validator->validate('10.0.1.1/22'));
    }

    public function testValidateRangeIPv6()
    {
        $validator = new IpValidator([
            'ranges' => '2001:db0:1:1::/64',
        ]);
        $this->assertTrue($validator->validate('2001:db0:1:1::6'));
        $this->assertFalse($validator->validate('2001:db0:1:2::7'));

        $validator->ranges = ['2001:db0:1:2::/64'];
        $this->assertTrue($validator->validate('2001:db0:1:2::7'));

        $validator->ranges = ['!2001:db0::/32', '2001:db0:1:2::/64'];
        $this->assertFalse($validator->validate('2001:db0:1:2::7'));

        $validator->subnet = null;
        $validator->ranges = array_reverse($validator->ranges);
        $this->assertTrue($validator->validate('2001:db0:1:2::7'));
    }

    public function testValidateRangeIPvBoth()
    {
        $validator = new IpValidator([
            'ranges' => '10.0.1.0/24',
        ]);
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('192.5.1.1'));
        $this->assertFalse($validator->validate('2001:db0:1:2::7'));

        $validator->ranges = ['10.0.1.0/24', '2001:db0:1:2::/64', '127.0.0.1'];
        $this->assertTrue($validator->validate('2001:db0:1:2::7'));
        $this->assertTrue($validator->validate('10.0.1.2'));
        $this->assertFalse($validator->validate('10.0.3.2'));

        $validator->ranges = ['!system', 'any'];
        $this->assertFalse($validator->validate('127.0.0.1'));
        $this->assertFalse($validator->validate('fe80::face'));
        $this->assertTrue($validator->validate('8.8.8.8'));

        $validator->subnet = null;
        $validator->ranges = ['10.0.1.0/24', '2001:db0:1:2::/64', 'localhost', '!all'];
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
}
