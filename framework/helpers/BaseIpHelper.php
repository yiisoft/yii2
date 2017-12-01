<?php

namespace yii\helpers;

/**
 * Class BaseIpHelper
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class BaseIpHelper
{
    const IPV4 = 4;
    const IPV6 = 6;
    /**
     * The length of IPv6 address in bits
     */
    const IPV6_ADDRESS_LENGTH = 128;
    /**
     * The length of IPv4 address in bits
     */
    const IPV4_ADDRESS_LENGTH = 32;

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
     * Checks whether the IP address is in the subnet range.
     *
     * @param string $ip the valid IPv4 or IPv6 address
     * @param int $cidr
     * @param string $range subnet in CIDR format e.g. `10.0.0.0/8` or `2001:af::/64`
     * @return bool
     */
    public static function inRange($ip, $cidr, $range)
    {
        $ipVersion = static::getIpVersion($ip);
        $binIp = static::ip2bin($ip);

        $parts = explode('/', $range);
        $net = array_shift($parts);
        $range_cidr = array_shift($parts);

        $netVersion = static::getIpVersion($net);
        if ($ipVersion !== $netVersion) {
            return false;
        }
        if ($range_cidr === null) {
            $range_cidr = $netVersion === 4 ? self::IPV4_ADDRESS_LENGTH : self::IPV6_ADDRESS_LENGTH;
        }

        $binNet = static::ip2bin($net);
        return substr($binIp, 0, $range_cidr) === substr($binNet, 0, $range_cidr) && $cidr >= $range_cidr;
    }

    /**
     * Expands an IPv6 address to it's full notation. For example `2001:db8::1` will be
     * expanded to `2001:0db8:0000:0000:0000:0000:0000:0001`
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
     * Converts IP address to bits representation
     *
     * @param string $ip the valid IPv4 or IPv6 address
     * @return string bits as a string
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
