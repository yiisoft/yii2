<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;

/**
 * IpAddressValidator validates that the attribute value is a valid IPv4/IPv6 address or subnet.
 * May change attribute's value if normalization is enabled.
 *
 * @author SilverFire <d.naumenko.a@gmail.com>
 * @since 2.0.4
 */
class IpAddressValidator extends Validator
{
    /**
     * @const integer the length of IPv6 address in bits
     */
    const IPV6_ADDRESS_LENGTH = 128;

    /**
     * @const integer the length of IPv4 address in bits
     */
    const IPV4_ADDRESS_LENGTH = 32;

    const RANGE_ORDER_ALLOWED_DENIED = 0;
    const RANGE_ORDER_DENIED_ALLOWED = 1;

    /**
     * @var boolean whether support of IPv6 addresses is enabled
     */
    public $ipv6 = true;

    /**
     * @var boolean whether support of IPv4 addresses is enabled
     */
    public $ipv4 = true;

    /**
     * @var boolean|string whether address may be CIDR subnet
     *     boolean - normal behaviour
     *     string  - value 'only' to validate only address with a CIDR
     */
    public $subnet = false;

    /**
     * @var boolean whether to add the prefix with smallest length (32 for IPv4 and 128 for IPv6)
     * to an address without it.
     * Works only when attribute 'subnet' is not false.
     * @see subnet
     */
    public $normalize = false;

    /**
     * @var boolean|string whether address may have an exclude-character at the beginning
     *   boolean - character "!" will be used
     *   string - passed character will be used
     */
    public $exclude = false;

    /**
     * @var boolean whether to expand an IPv6 address to full notation format
     */
    public $expandV6 = false;

    /**
     * @var int the order of ranges rules. Used by [[checkRanges]]
     * @see checkRanges
     */
    public $rangesOrder = self::RANGE_ORDER_DENIED_ALLOWED;

    /**
     * @var string|array IPv4 or IPv6 ranges that are allowed to use. For example:
     *
     * ```
     * ['10.0.0.0/8', '192.168.1.1', '2001:ab::/64', '2001:ac::2:1']
     * ```
     *
     * @see checkAllowed
     */
    public $allowedRanges = null;

    /**
     * @var string|array IPv4 or IPv6 ranges that are prohibited to use. For example:
     *
     * ```
     * ['10.0.0.0/24', '192.168.2.1', '2001:ab::/32', '2001:ac::1:2']
     * ```
     *
     * @see checkAllowed
     */
    public $deniedRanges = null;

    /**
     * @var string Regexp-pattern to validate IPv4 address
     */
    public $ipv4Pattern = '/^(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))$/';

    /**
     * @var string Regexp-pattern to validate IPv6 address
     */
    public $ipv6Pattern = '/^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/';

    /**
     * @var string user-defined error message is used when validation fails due to the disabled IPv6 validation
     */
    public $ipv6NotAllowed = "{attribute} should not contain an IPv6 address";

    /**
     * @var string user-defined error message is used when validation fails due to the disabled IPv4 validation
     */
    public $ipv4NotAllowed = "{attribute} should not contain an IPv4 address";

    /**
     * @var string user-defined error message is used when validation fails due to the wrong CIDR
     */
    public $wrongCidr = "{attribute} contains wrong subnet mask";

    /**
     * @var string user-defined error message is used when validation fails due to the wrong IP address format
     */
    public $wrongIp = "{attribute} should contain a valid IP address";

    /**
     * @var string user-defined error message is used when validation fails due to subnet [[subnet]] set to 'only',
     * but the CIDR prefix is not set
     * @see subnet
     */
    public $noSubnet = "{attribute} should contain an IP address with specified subnet";

    /**
     * @var string user-defined error message is used when validation fails
     * due to [[subnet]] is false, but CIDR prefix is present
     * @see subnet
     */
    public $hasSubnet = "{attribute} should not be a subnet";

    /**
     * @var string user-defined error message is used when validation fails due to IP address
     * is not on the [[allowedRanges]] list, or is in the [[deniedRanges]] list
     * @see allowedRanges
     * @see deniedRanges
     */
    public $notInRange = "{attribute} is not in the allowed range";

    /**
     * @var array temporary variable contains the last error messages until they are processed
     */
    protected $tempMessages;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            if ($this->subnet) {
                $this->message = Yii::t('app', '{attribute} is not a valid IP subnet');
            } else {
                $this->message = Yii::t('app', '{attribute} is not a valid IP address');
            }
        }

        if (!$this->ipv4 && !$this->ipv6) {
            throw new InvalidConfigException('Both IPv4 and IPv6 checks can not be disabled at same time');
        }

        if (!defined('AF_INET6') && $this->ipv6) {
            throw new InvalidConfigException('IPv6 validation can not be used. PHP is compiled without IPv6');
        }

        if (!empty($this->allowedRanges) && !is_array($this->allowedRanges)) {
            $this->allowedRanges = (array)$this->allowedRanges;
        }

        if (!empty($this->deniedRanges) && !is_array($this->deniedRanges)) {
            $this->deniedRanges = (array)$this->deniedRanges;
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $result = $this->validateSubnet($value);
        if ($result !== false) {
            return null;
        } else {
            return [$this->removeTempMessage(), []];
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $result = $this->validateSubnet($value);
        if ($result === false) {
            $this->addError($model, $attribute, $this->removeTempMessage());
        } else {
            $model->$attribute = $result;
        }
    }

    /**
     * Validates an IPv4/IPv6 address or subnet
     *
     * @param $ip string
     * @return boolean|string
     */
    public function validateSubnet($ip)
    {
        if (!is_string($ip)) {
            return $this->setTempMessage($this->wrongIp);
        }

        $exclude = null;
        $cidr = null;

        $exclude_character = is_string($this->exclude) ? preg_quote($this->exclude, '/') : '!';
        if (preg_match("/^($exclude_character?)(.+?)(\/(\d+))?$/", $ip, $matches)) {
            $exclude = ($matches[1] !== '') ? $matches[1] : null;
            $ip = $matches[2];
            $cidr = isset($matches[4]) ? $matches[4] : null;
        }

        if ($this->subnet === 'only' && $cidr == null) {
            return $this->setTempMessage($this->noSubnet);
        }
        if (!$this->subnet && $cidr !== null) {
            return $this->setTempMessage($this->hasSubnet);
        }
        if ($this->exclude === false && $exclude !== null) {
            return $this->setTempMessage($this->wrongIp);
        }

        if ($this->getIpVersion($ip) == 6) {
            if ($cidr !== null) {
                if ($cidr > static::IPV6_ADDRESS_LENGTH || $cidr < 0) {
                    return $this->setTempMessage($this->wrongCidr);
                }
            } elseif ($this->normalize) {
                $cidr = static::IPV6_ADDRESS_LENGTH;
            }

            if (!$this->ipv6) {
                return $this->setTempMessage($this->ipv6NotAllowed);
            }
            if (!$this->validate6($ip)) {
                return $this->setTempMessage($this->wrongIp);
            }

            if ($this->expandV6) {
                $ip = $this->expand6($ip);
            }
        } else {
            if ($cidr !== null) {
                if ($cidr > static::IPV4_ADDRESS_LENGTH || $cidr < 0) {
                    return $this->setTempMessage($this->wrongCidr);
                }
            } elseif ($this->normalize) {
                $cidr = static::IPV4_ADDRESS_LENGTH;
            }

            if (!$this->ipv4) {
                return $this->setTempMessage($this->ipv4NotAllowed);
            }
            if (!$this->validate4($ip)) {
                return $this->setTempMessage($this->wrongIp);
            }
        }

        if (!$this->checkRanges($ip)) {
            return $this->setTempMessage($this->notInRange);
        }

        $result = $exclude . $ip;

        if ($this->subnet) {
            $result .= "/$cidr";
        }

        return $result;
    }

    /**
     * Expands an IPv6 address to it's full notation. For example `2001:db8::1` will be
     * expanded to `2001:0db8:0000:0000:0000:0000:0000:0001`
     *
     * @param $ip string the original IPv6
     * @return string the expanded IPv6
     */
    public static function expand6($ip)
    {
        $hex = unpack("H*hex", inet_pton($ip));
        return substr(preg_replace("/([a-f0-9]{4})/i", "$1:", $hex['hex']), 0, -1);
    }

    /**
     * Checks whether IP address can be used according to [[deniedRanges]] and [[allowedRanges]] lists
     * and [[rangesOrder]] option.
     *
     * When [[rangesOrder]] is [[RANGE_ORDER_ALLOWED_DENIED]] - checks all [[allowedRanges]], at least one must
     * match or will return false. Next checks all [[deniedRanges]], if one of them matched - will return false.
     * At last, if $ip is not in [[allowedRanges]] nor in [[deniedRanges]] - will return false.
     *
     * When [[RANGE_ORDER_DENIED_ALLOWED]] - checks all [[deniedRanges]] and [[allowedRanges]].
     * If the value is in the [[deniedRanges]] will return false, unless it is also present in [[allowedRanges]].
     * If not found in the both of lists - will return true.
     *
     * Tip: it is useful to use [[RANGE_ORDER_ALLOWED_DENIED]], when need to deny a less specific subnet and
     * allow a more specific one. Example below will cause passing `192.168.1.1`, but `192.168.2.1` will be denied:
     *
     * ```
     * [
     *      'deniedRanges' => ['192.168.0.0/16'],
     *      'allowedRanges' => ['192.168.1.0/24']
     * ]
     * ```
     *
     *
     * @param $ip string
     * @return boolean
     * @see rangesOrder
     */
    public function checkRanges($ip)
    {
        $denied = false;
        $allowed = true;
        if (!empty($this->deniedRanges) && $this->isIpInRange($ip, $this->deniedRanges)) {
            $denied = true;
        }
        if (!empty($this->allowedRanges) && !$this->isIpInRange($ip, $this->allowedRanges)) {
            $allowed = false;
        }

        return $this->rangesOrder === self::RANGE_ORDER_DENIED_ALLOWED ? (!$denied && $allowed) : (!$denied || $allowed);
    }

    /**
     * Validates IPv4 address
     *
     * @param $value string
     * @return boolean
     */
    public function validate4($value)
    {
        return preg_match($this->ipv4Pattern, $value) !== 0;
    }

    /**
     * Validates IPv6 address
     *
     * @param $value string
     * @return boolean
     */
    public function validate6($value)
    {
        return preg_match($this->ipv6Pattern, $value) !== 0;
    }

    /**
     * Gets the IP version
     *
     * @param $ip string
     * @return integer
     */
    public static function getIpVersion($ip)
    {
        return strpos($ip, ":") === false ? 4 : 6;
    }

    /**
     * Checks whether the IP is in subnet ranges
     *
     * @param $ip string an IPv4 or IPv6 address
     * @param $ranges string|array allowed subnets in CIDR format e.g. `10.0.0.0/8` or `2001:af::/64`
     * @return bool
     */
    public function isIpInRange($ip, $ranges)
    {
        $ranges = (array)$ranges;
        $ipVersion = $this->getIpVersion($ip);
        $binIp = $this->ip2bin($ip);
        foreach ($ranges as $range) {
            $parts = explode('/', $range);
            $net = array_shift($parts);
            $cidr = array_shift($parts);

            $netVersion = $this->getIpVersion($net);
            if ($ipVersion != $netVersion) {
                continue;
            }
            if ($cidr === null) {
                $cidr = $netVersion == 4 ? static::IPV4_ADDRESS_LENGTH : static::IPV6_ADDRESS_LENGTH;
            }

            $binNet = $this->ip2bin($net);
            if (substr($binIp, 0, $cidr) == substr($binNet, 0, $cidr)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Converts IP address to bits representation
     *
     * @param $ip string
     * @return string string of bits
     */
    public static function ip2bin($ip)
    {
        if (static::getIpVersion($ip) == 4) {
            return str_pad(base_convert(ip2long($ip), 10, 2), static::IPV4_ADDRESS_LENGTH, '0', STR_PAD_LEFT);
        } else {
            $unpack = unpack("A16", inet_pton($ip));
            $binStr = array_shift($unpack);
            $bytes = static::IPV6_ADDRESS_LENGTH / 8; // 128 bit / 8 = 16 bytes
            $result = '';
            while ($bytes-- > 0) {
                $result = sprintf("%08b", isset($binStr[$bytes]) ? ord($binStr[$bytes]) : '0') . $result;
            }
            return $result;
        }
    }

    /**
     * Sets temporary error message for current validation and returns false
     *
     * @param $message string
     * @return boolean always false
     */
    public function setTempMessage($message)
    {
        $this->tempMessage = $message;
        return false;
    }

    /**
     * Removes temp message
     *
     * @return mixed the removed temporary message. Null if the temp message does not exist.
     */
    public function removeTempMessage()
    {
        $value = $this->tempMessage;
        $this->tempMessage = null;
        return $value;
    }
}