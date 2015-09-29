<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * IpValidator validates that the attribute value is a valid IPv4/IPv6 address or subnet.
 * May change attribute's value if normalization is enabled.
 *
 * @property boolean|string negationChar whether address may have a negation character at the beginning
 *   string - character passed will be used. May be a regular expression
 *   true - character from [[DEFAULT_NEGATION_CHAR]] will be used. Default is ```!```
 *   false  - negation is not allowed
 *
 * @author SilverFire <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class IpValidator extends Validator
{
    /**
     * The length of IPv6 address in bits
     */
    const IPV6_ADDRESS_LENGTH = 128;

    /**
     * The length of IPv4 address in bits
     */
    const IPV4_ADDRESS_LENGTH = 32;

    /**
     * Default negation char when [[negationChar]] is set to `true`
     * @see negationChar
     */
    const DEFAULT_NEGATION_CHAR = '!';

    /**
     * @var boolean whether support of IPv6 addresses is enabled
     */
    public $ipv6 = true;

    /**
     * @var boolean whether support of IPv4 addresses is enabled
     */
    public $ipv4 = true;

    /**
     * @var boolean whether the address can be a CIDR subnet
     *    true - the subnet is required
     *   false - the address can not have the subnet
     *    null - ths subnet is optional
     */
    public $subnet = false;

    /**
     * @var boolean whether to add the prefix with the smallest length (32 for IPv4 and 128 for IPv6)
     * to an address without it.
     * Works only when attribute [[subnet]] is not false.
     * Example: `10.0.1.5` will normalized to `10.0.1.5/32`, `2008:db0::1` will be normalized to `2008:db0::1/128`
     * @see subnet
     */
    public $normalize = false;

    /**
     * @var string|boolean
     * @see negationChar
     */
    public $_negationChar = false;

    /**
     * @var boolean whether to expand an IPv6 address to the full notation format
     */
    public $expandIPv6 = false;

    /**
     * @var array IPv4 or IPv6 ranges that are allowed or denied.
     *
     * The array should contain only ```allow``` or/and  ```deny``` keys.
     * Their order determines the order of checks of the IP address.
     *
     * When the first is ```allow``` - all the ```allow``` IP addresses will be checked, and at least one must
     * match or the validation will fail. Then all the ```deny``` (if present) will be checked, if one of them
     * matched the validation will fail.
     * At last, if the IP address is neither on the ```allow`` nor on the ```deny```, the validation will also fail.
     *
     * When the first is ```deny``` - all the ```deny``` IP addresses will be checked, if one matched the validation
     * will fail, unless it is also present on the ```allow```.
     * If it is not found on any of the lists the validation will be passed.
     *
     * Tip: it is useful to set ```deny``` first, when it is necessary to deny a less specific subnet and
     * to allow a more specific one. The example below will result in passing `192.168.1.1`,
     * but `192.168.2.1` will be denied:
     *
     * ```
     * 'ips' => [
     *     'deny' => ['192.168.0.0/16'],
     *     'allow' => ['192.168.1.0/24']
     * ]
     * ```
     *
     * @see isAllowed()
     */
    public $ips = [];

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
    public $ipv6NotAllowed = '{attribute} must not be an IPv6 address';

    /**
     * @var string user-defined error message is used when validation fails due to the disabled IPv4 validation
     */
    public $ipv4NotAllowed = '{attribute} must not be an IPv4 address';

    /**
     * @var string user-defined error message is used when validation fails due to the wrong CIDR
     */
    public $wrongCidr = '{attribute} contains wrong subnet mask';

    /**
     * @var string user-defined error message is used when validation fails due to the wrong IP address format
     */
    public $wrongIp = '{attribute} must be a valid IP address';

    /**
     * @var string user-defined error message is used when validation fails due to subnet [[subnet]] set to 'only',
     * but the CIDR prefix is not set
     * @see subnet
     */
    public $noSubnet = '{attribute} must be an IP address with specified subnet';

    /**
     * @var string user-defined error message is used when validation fails
     * due to [[subnet]] is false, but CIDR prefix is present
     * @see subnet
     */
    public $hasSubnet = '{attribute} must not be a subnet';

    /**
     * @var string user-defined error message is used when validation fails due to IP address
     * is not on the [[allow]] list, or is on the [[deny]] list
     * @see ips
     */
    public $notInRange = '{attribute} is not in the allowed range';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->ipv4 && !$this->ipv6) {
            throw new InvalidConfigException('Both IPv4 and IPv6 checks can not be disabled at the same time');
        }

        if (!defined('AF_INET6') && $this->ipv6) {
            throw new InvalidConfigException('IPv6 validation can not be used. PHP is compiled without IPv6');
        }

        if (!empty($this->ips['allow']) && !is_array($this->ips['allow'])) {
            $this->ips['allow'] = (array)$this->ips['allow'];
        }

        if (!empty($this->ips['deny']) && !is_array($this->ips['deny'])) {
            $this->ips['deny'] = (array)$this->ips['deny'];
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $result = $this->validateSubnet($value);
        if (is_array($result)) {
            $result[1] = array_merge(['ip' => is_array($value) ? 'array()' : $value], $result[1]);
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $result = $this->validateSubnet($value);
        if (is_array($result)) {
            $result[1] = array_merge(['ip' => is_array($value) ? 'array()' : $value], $result[1]);
            $this->addError($model, $attribute, $result[0], $result[1]);
        } else {
            $model->$attribute = $result;
        }
    }

    /**
     * Validates an IPv4/IPv6 address or subnet
     *
     * @param $ip string
     * @return string|array
     *  string - the validation was successful;
     *  array  - an error occurred during the validation.
     * Array[0] contains the text of an error, array[1] contains values for the placeholders in the error message
     */
    private function validateSubnet($ip)
    {
        if (!is_string($ip)) {
            return [$this->wrongIp, []];
        }

        $negation = null;
        $cidr = null;
        $isCidrDefault = false;

        if (preg_match($this->getIpParsePattern(), $ip, $matches)) {
            $negation = ($matches[1] !== '') ? $matches[1] : null;
            $ip = $matches[2];
            $cidr = isset($matches[4]) ? $matches[4] : null;
        }

        if ($this->subnet === true && $cidr === null) {
            return [$this->noSubnet, []];
        }
        if ($this->subnet === false && $cidr !== null) {
            return [$this->hasSubnet, []];
        }
        if ($this->negationChar === false && $negation !== null) {
            return [$this->wrongIp, []];
        }

        if ($this->getIpVersion($ip) == 6) {
            if ($cidr !== null) {
                if ($cidr > static::IPV6_ADDRESS_LENGTH || $cidr < 0) {
                    return [$this->wrongCidr, []];
                }
            } else {
                $isCidrDefault = true;
                $cidr = static::IPV6_ADDRESS_LENGTH;
            }

            if (!$this->ipv6) {
                return [$this->ipv6NotAllowed, []];
            }
            if (!$this->validateIPv6($ip)) {
                return [$this->wrongIp, []];
            }

            if ($this->expandIPv6) {
                $ip = $this->expandIPv6($ip);
            }
        } else {
            if ($cidr !== null) {
                if ($cidr > static::IPV4_ADDRESS_LENGTH || $cidr < 0) {
                    return [$this->wrongCidr, []];
                }
            } else {
                $isCidrDefault = true;
                $cidr = static::IPV4_ADDRESS_LENGTH;
            }

            if (!$this->ipv4) {
                return [$this->ipv4NotAllowed, []];
            }
            if (!$this->validateIPv4($ip)) {
                return [$this->wrongIp, []];
            }
        }

        if (!$this->isAllowed($ip, $cidr)) {
            return [$this->notInRange, []];
        }

        $result = $negation . $ip;

        if ($this->subnet !== false && (!$isCidrDefault || $isCidrDefault && $this->normalize)) {
            $result .= "/$cidr";
        }

        return $result;
    }

    /**
     * Expands an IPv6 address to it's full notation. For example `2001:db8::1` will be
     * expanded to `2001:0db8:0000:0000:0000:0000:0000:0001`
     *
     * @param string $ip the original IPv6
     * @return string the expanded IPv6
     */
    private function expandIPv6($ip)
    {
        $hex = unpack('H*hex', inet_pton($ip));
        return substr(preg_replace('/([a-f0-9]{4})/i', '$1:', $hex['hex']), 0, -1);
    }

    /**
     * The method checks whether the IP address is allowed according to the [[ips]] lists
     *
     * @param string $ip
     * @param integer $cidr
     * @return boolean
     * @see ips
     */
    private function isAllowed($ip, $cidr)
    {
        $denied = false;
        $allowed = true;
        if (!empty($this->ips['deny']) && $this->inRange($ip, $cidr, $this->ips['deny'])) {
            $denied = true;
        }
        if (!empty($this->ips['allow']) && !$this->inRange($ip, $cidr, $this->ips['allow'])) {
            $allowed = false;
        }

        return current(array_keys($this->ips)) === 'allow' ? (!$denied && $allowed) : (!$denied || $allowed);
    }

    /**
     * Validates IPv4 address
     *
     * @param string $value
     * @return boolean
     */
    protected function validateIPv4($value)
    {
        return preg_match($this->ipv4Pattern, $value) !== 0;
    }

    /**
     * Validates IPv6 address
     *
     * @param string $value
     * @return boolean
     */
    protected function validateIPv6($value)
    {
        return preg_match($this->ipv6Pattern, $value) !== 0;
    }

    /**
     * Gets the IP version
     *
     * @param string $ip
     * @return integer
     */
    private function getIpVersion($ip)
    {
        return strpos($ip, ':') === false ? 4 : 6;
    }

    /**
     * Used to get the Regexp pattern for initial IP address parsing
     * @return string
     */
    private function getIpParsePattern()
    {
        $negationChar = is_string($this->negationChar) ? $this->negationChar : static::DEFAULT_NEGATION_CHAR;
        return '/^(' . $negationChar . '?)(.+?)(\/(\d+))?$/';
    }

    /**
     * Method returns current negation character
     *
     * @return string
     * @see _negationChar
     */
    public function getNegationChar()
    {
        return $this->_negationChar;
    }

    /**
     * Setter for [[_negationChar]].
     * Quotes string to be used in regular expressions
     *
     * @param string|boolean $negationChar
     * @see _negationChar
     */
    public function setNegationChar($negationChar)
    {
        if (is_string($this->negationChar)) {
            $negationChar = preg_quote($this->_negationChar, '/');
        }
        $this->_negationChar = $negationChar;
    }


    /**
     * Checks whether the IP is in subnet ranges
     *
     * @param string $ip an IPv4 or IPv6 address
     * @param integer $cidr
     * @param string|array $ranges allowed subnets in CIDR format e.g. `10.0.0.0/8` or `2001:af::/64`
     * @return bool
     */
    private function inRange($ip, $cidr, $ranges)
    {
        $ranges = (array)$ranges;
        $ipVersion = $this->getIpVersion($ip);
        $binIp = $this->ip2bin($ip);
        foreach ($ranges as $range) {
            $parts = explode('/', $range);
            $net = array_shift($parts);
            $range_cidr = array_shift($parts);

            $netVersion = $this->getIpVersion($net);
            if ($ipVersion !== $netVersion) {
                continue;
            }
            if ($range_cidr === null) {
                $range_cidr = $netVersion === 4 ? static::IPV4_ADDRESS_LENGTH : static::IPV6_ADDRESS_LENGTH;
            }

            $binNet = $this->ip2bin($net);
            if (substr($binIp, 0, $range_cidr) === substr($binNet, 0, $range_cidr) && $cidr >= $range_cidr) {
                return true;
            }
        }
        return false;
    }

    /**
     * Converts IP address to bits representation
     *
     * @param string $ip
     * @return string bits as a string
     */
    private function ip2bin($ip)
    {
        if ($this->getIpVersion($ip) === 4) {
            return str_pad(base_convert(ip2long($ip), 10, 2), static::IPV4_ADDRESS_LENGTH, '0', STR_PAD_LEFT);
        } else {
            $unpack = unpack('A16', inet_pton($ip));
            $binStr = array_shift($unpack);
            $bytes = static::IPV6_ADDRESS_LENGTH / 8; // 128 bit / 8 = 16 bytes
            $result = '';
            while ($bytes-- > 0) {
                $result = sprintf('%08b', isset($binStr[$bytes]) ? ord($binStr[$bytes]) : '0') . $result;
            }
            return $result;
        }
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $messages = [
            'ipv6NotAllowed' => $this->ipv6NotAllowed,
            'ipv4NotAllowed' => $this->ipv4NotAllowed,
            'wrongIp' => $this->wrongIp,
            'noSubnet' => $this->noSubnet
        ];
        foreach ($messages as &$message) {
            $message = Yii::$app->getI18n()->format($message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ], Yii::$app->language);
        }

        $options = [
            'ipv4Pattern' => new JsExpression($this->ipv4Pattern),
            'ipv6Pattern' => new JsExpression($this->ipv6Pattern),
            'messages' => $messages,
            'ipv4' => (boolean)$this->ipv4,
            'ipv6' => (boolean)$this->ipv6,
            'ipParsePattern' => Html::escapeJsRegularExpression($this->getIpParsePattern()),
            'negationChar' => $this->negationChar,
            'subnet' => $this->subnet
        ];

        ValidationAsset::register($view);

        return 'yii.validation.ip(value, messages, ' . Json::htmlEncode($options) . ');';
    }
}
