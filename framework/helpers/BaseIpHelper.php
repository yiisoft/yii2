<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * BaseIpHelper 类为 [[IpHelper]] 提供了具体的实现方法
 *
 * 不要使用 BaseIpHelper，使用 [[IpHelper]] 类来代替。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class BaseIpHelper
{
    const IPV4 = 4;
    const IPV6 = 6;
    /**
     * IPv6 地址使用 bits 表示的长度
     */
    const IPV6_ADDRESS_LENGTH = 128;
    /**
     * IPv4 地址使用 bits 表示的长度
     */
    const IPV4_ADDRESS_LENGTH = 32;


    /**
     * 获取 IP 版本。不执行 IP 地址验证。
     *
     * @param string $ip 有效地 IPv4 或者 IPv6 地址。
     * @return int [[IPV4]] 或者 [[IPV6]]
     */
    public static function getIpVersion($ip)
    {
        return strpos($ip, ':') === false ? self::IPV4 : self::IPV6;
    }

    /**
     * 检测 IP 地址是否正确或者子网 $subnet 包含了 $subnet。
     *
     * 例如，下面的代码检查子网 `192.168.1.0/24` 是否存在子网 `192.168.0.0/22`：
     *
     * ```php
     * IpHelper::inRange('192.168.1.0/24', '192.168.0.0/22'); // true
     * ```
     *
     * 如果您需要检查单个 IP 地址 `192.168.1.21` 在子网 `192.168.1.0/24` 中，
     * 你可以用这些例子中的任何一个：
     *
     * ```php
     * IpHelper::inRange('192.168.1.21', '192.168.1.0/24'); // true
     * IpHelper::inRange('192.168.1.21/32', '192.168.1.0/24'); // true
     * ```
     *
     * @param string $subnet 有效地 IPv4 或者 IPv6 地址或者 CIDR 范围，例如：`10.0.0.0/8` 或者 `2001:af::/64`
     * @param string $range 有效地 IPv4 或者 IPv6 CIDR 范围，例如 `10.0.0.0/8` 或者 `2001:af::/64`
     * @return bool $subnet 是否包含 $range 中 
     *
     * @see https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing
     */
    public static function inRange($subnet, $range)
    {
        list($ip, $mask) = array_pad(explode('/', $subnet), 2, null);
        list($net, $netMask) = array_pad(explode('/', $range), 2, null);

        $ipVersion = static::getIpVersion($ip);
        $netVersion = static::getIpVersion($net);
        if ($ipVersion !== $netVersion) {
            return false;
        }

        $maxMask = $ipVersion === self::IPV4 ? self::IPV4_ADDRESS_LENGTH : self::IPV6_ADDRESS_LENGTH;
        $mask = isset($mask) ? $mask : $maxMask;
        $netMask = isset($netMask) ? $netMask : $maxMask;

        $binIp = static::ip2bin($ip);
        $binNet = static::ip2bin($net);
        return substr($binIp, 0, $netMask) === substr($binNet, 0, $netMask) && $mask >= $netMask;
    }

    /**
     * 将 IPv6 的地址扩展为完整的形式。
     *
     * 例如 `2001:db8::1` 将被展开成 `2001:0db8:0000:0000:0000:0000:0000:0001` 这种形式
     *
     * @param string $ip 原始有效的 IPv6 地址
     * @return string 展开的 IPv6 地址
     */
    public static function expandIPv6($ip)
    {
        $hex = unpack('H*hex', inet_pton($ip));
        return substr(preg_replace('/([a-f0-9]{4})/i', '$1:', $hex['hex']), 0, -1);
    }

    /**
     * 将 IP 转换成 bits 形式来表示。
     *
     * @param string $ip 有效的 IPv4 或者 IPv6 地址
     * @return string bits 用字符串来表示
     */
    public static function ip2bin($ip)
    {
        if (static::getIpVersion($ip) === self::IPV4) {
            return str_pad(base_convert(ip2long($ip), 10, 2), self::IPV4_ADDRESS_LENGTH, '0', STR_PAD_LEFT);
        }

        $unpack = unpack('A16', inet_pton($ip));
        $binStr = array_shift($unpack);
        $bytes = self::IPV6_ADDRESS_LENGTH / 8; // 128 bit / 8 = 16 bytes
        $result = '';
        while ($bytes-- > 0) {
            $result = sprintf('%08b', isset($binStr[$bytes]) ? ord($binStr[$bytes]) : '0') . $result;
        }
        return $result;
    }
}
