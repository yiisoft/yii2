<?php

namespace yiiunit\framework\helpers;

use yii\helpers\IpHelper;
use yiiunit\TestCase;

/**
 * Class IpHelperTest
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class IpHelperTest extends TestCase
{
    /**
     * @dataProvider getIpVersionProvider
     * @param $value
     * @param $expected
     */
    public function testGetIpVersion($value, $expected, $message = '')
    {
        $version = IpHelper::getIpVersion($value);
        $this->assertSame($expected, $version, $message);
    }

    public function getIpVersionProvider()
    {
        return [
            ['192.168.0.1', IpHelper::IPV4],
            ['192.168.0.1/24', IpHelper::IPV4, 'IPv4 with CIDR is resolved correctly'],
            ['fb01::1', IpHelper::IPV6],
            ['fb01::1/24', IpHelper::IPV6, 'IPv6 with CIDR is resolved correctly'],
            ['', IpHelper::IPV4, 'Empty string is treated as IPv4'],
        ];
    }

    /**
     * @dataProvider expandIpv6Provider
     */
    public function testExpandIpv6($value, $expected, $message = '')
    {
        $expanded = IpHelper::expandIPv6($value);
        $this->assertSame($expected, $expanded, $message);
    }

    public function expandIpv6Provider()
    {
        return [
            ['fa01::1', 'fa01:0000:0000:0000:0000:0000:0000:0001'],
            ['2001:db0:1:2::7', '2001:0db0:0001:0002:0000:0000:0000:0007'],
        ];
    }

    public function testIpv6ExpandingWithInvalidValue()
    {
        try {
            IpHelper::expandIPv6('fa01::1/64');
        } catch (\Exception $exception) {
            $this->assertStringEndsWith('Unrecognized address fa01::1/64', $exception->getMessage());
        }
    }

    /**
     * @param $value
     * @param $expected
     * @param string $message
     *
     * @dataProvider ip2binProvider
     */
    public function testIp2bin($value, $expected, $message = '')
    {
        $result = IpHelper::ip2bin($value);
        $this->assertSame($expected, $result, $message);
    }

    public function ip2binProvider()
    {
        return [
            ['192.168.1.1', '11000000101010000000000100000001'],
            ['', '00000000000000000000000000000000'],
            ['fa01:0000:0000:0000:0000:0000:0000:0001', '11111010000000010000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001'],
            ['fa01::1', '11111010000000010000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001']
        ];
    }

    /**
     * @param $value
     * @param $range
     * @param $expected
     *
     * @dataProvider inRangeProvider
     */
    public function testInRange($value, $range, $expected)
    {
        $result = IpHelper::inRange($value, $range);
        $this->assertSame($expected, $result);
    }

    public function inRangeProvider()
    {
        return [
            ['192.168.1.1/24', '192.168.0.0/23', true],
            ['192.168.1.1/24', '192.168.0.0/24', false],
            ['192.168.1.1/24', '0.0.0.0/0', true],
            ['192.168.1.1/32', '192.168.1.1', true],
            ['192.168.1.1/32', '192.168.1.1/32', true],
            ['192.168.1.1', '192.168.1.1/32', true],
            ['fa01::1/128', 'fa01::/64', true],
            ['fa01::1/128', 'fa01::1/128', true],
            ['fa01::1/64', 'fa01::1/128', false],
        ];
    }
}
