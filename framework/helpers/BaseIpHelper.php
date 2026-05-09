<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\NotSupportedException;

/**
 * Class BaseIpHelper provides concrete implementation for [[IpHelper]]
 *
 * Do not use BaseIpHelper, use [[IpHelper]] instead.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class BaseIpHelper
{
    public const IPV4 = 4;
    public const IPV6 = 6;
    /**
     * The length of IPv6 address in bits
     */
    public const IPV6_ADDRESS_LENGTH = 128;
    /**
     * The length of IPv4 address in bits
     */
    public const IPV4_ADDRESS_LENGTH = 32;
    /**
     * Gets the IP version. Does not perform IP address validation.
     *
     * @param string $ip the valid IPv4 or IPv6 address.
     * @return int [[IPV4]] or [[IPV6]]
     */
    public static function getIpVersion($ip)
    {
        return strpos($ip, ':') === false ? self::IPV4 : self::IPV6;
    }

    /**
     * Checks whether IP address or subnet $subnet is contained by $subnet.
     *
     * For example, the following code checks whether subnet `192.168.1.0/24` is in subnet `192.168.0.0/22`:
     *
     * ```
     * IpHelper::inRange('192.168.1.0/24', '192.168.0.0/22'); // true
     * ```
     *
     * In case you need to check whether a single IP address `192.168.1.21` is in the subnet `192.168.1.0/24`,
     * you can use any of theses examples:
     *
     * ```
     * IpHelper::inRange('192.168.1.21', '192.168.1.0/24'); // true
     * IpHelper::inRange('192.168.1.21/32', '192.168.1.0/24'); // true
     * ```
     *
     * @param string $subnet the valid IPv4 or IPv6 address or CIDR range, e.g.: `10.0.0.0/8` or `2001:af::/64`
     * @param string $range the valid IPv4 or IPv6 CIDR range, e.g. `10.0.0.0/8` or `2001:af::/64`
     * @return bool whether $subnet is contained by $range
     *
     * @throws NotSupportedException
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
     * Expands an IPv6 address to it's full notation.
     *
     * For example `2001:db8::1` will be expanded to `2001:0db8:0000:0000:0000:0000:0000:0001`
     *
     * @param string $ip the original valid IPv6 address
     * @return string the expanded IPv6 address
     */
    public static function expandIPv6($ip)
    {
        $hex = unpack('H*hex', inet_pton($ip));
        return substr(preg_replace('/([a-f0-9]{4})/i', '$1:', $hex['hex']), 0, -1);
    }

    /**
     * Converts IP address to bits representation.
     *
     * @param string $ip the valid IPv4 or IPv6 address
     * @return string bits as a string
     * @throws NotSupportedException
     */
    public static function ip2bin($ip)
    {
        $ipBinary = null;
        if (static::getIpVersion($ip) === self::IPV4) {
            $ipBinary = pack('N', ip2long($ip));
        } elseif (@inet_pton('::1') === false) {
            throw new NotSupportedException('IPv6 is not supported by inet_pton()!');
        } else {
            $ipBinary = inet_pton($ip);
        }

        $result = '';
        for ($i = 0, $iMax = strlen($ipBinary); $i < $iMax; $i += 4) {
            $result .= str_pad(decbin(unpack('N', substr($ipBinary, $i, 4))[1]), 32, '0', STR_PAD_LEFT);
        }
        return $result;
    }
}
