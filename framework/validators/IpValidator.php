<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\IpHelper;

/**
 * The validator checks if the attribute value is a valid IPv4/IPv6 address or subnet.
 *
 * It also may change attribute's value if normalization of IPv6 expansion is enabled.
 *
 * The following are examples of validation rules using this validator:
 *
 * ```php
 * ['ip_address', 'ip'], // IPv4 or IPv6 address
 * ['ip_address', 'ip', 'ipv6' => false], // IPv4 address (IPv6 is disabled)
 * ['ip_address', 'ip', 'subnet' => true], // requires a CIDR prefix (like 10.0.0.1/24) for the IP address
 * ['ip_address', 'ip', 'subnet' => null], // CIDR prefix is optional
 * ['ip_address', 'ip', 'subnet' => null, 'normalize' => true], // CIDR prefix is optional and will be added when missing
 * ['ip_address', 'ip', 'ranges' => ['192.168.0.0/24']], // only IP addresses from the specified subnet are allowed
 * ['ip_address', 'ip', 'ranges' => ['!192.168.0.0/24', 'any']], // any IP is allowed except IP in the specified subnet
 * ['ip_address', 'ip', 'expandIPv6' => true], // expands IPv6 address to a full notation format
 * ```
 *
 * @property array $ranges The IPv4 or IPv6 ranges that are allowed or forbidden. See [[setRanges()]] for
 * detailed description.
 *
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class IpValidator extends Validator
{
    /**
     * Negation char.
     *
     * Used to negate [[ranges]] or [[networks]] or to negate validating value when [[negation]] is set to `true`.
     * @see negation
     * @see networks
     * @see ranges
     */
    const NEGATION_CHAR = '!';

    /**
     * @var array The network aliases, that can be used in [[ranges]].
     *  - key - alias name
     *  - value - array of strings. String can be an IP range, IP address or another alias. String can be
     *    negated with [[NEGATION_CHAR]] (independent of `negation` option).
     *
     * The following aliases are defined by default:
     *  - `*`: `any`
     *  - `any`: `0.0.0.0/0, ::/0`
     *  - `private`: `10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, fd00::/8`
     *  - `multicast`: `224.0.0.0/4, ff00::/8`
     *  - `linklocal`: `169.254.0.0/16, fe80::/10`
     *  - `localhost`: `127.0.0.0/8', ::1`
     *  - `documentation`: `192.0.2.0/24, 198.51.100.0/24, 203.0.113.0/24, 2001:db8::/32`
     *  - `system`: `multicast, linklocal, localhost, documentation`
     */
    public $networks = [
        '*' => ['any'],
        'any' => ['0.0.0.0/0', '::/0'],
        'private' => ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', 'fd00::/8'],
        'multicast' => ['224.0.0.0/4', 'ff00::/8'],
        'linklocal' => ['169.254.0.0/16', 'fe80::/10'],
        'localhost' => ['127.0.0.0/8', '::1'],
        'documentation' => ['192.0.2.0/24', '198.51.100.0/24', '203.0.113.0/24', '2001:db8::/32'],
        'system' => ['multicast', 'linklocal', 'localhost', 'documentation'],
    ];
    /**
     * @var bool whether the validating value can be an IPv6 address. Defaults to `true`.
     */
    public $ipv6 = true;
    /**
     * @var bool whether the validating value can be an IPv4 address. Defaults to `true`.
     */
    public $ipv4 = true;
    /**
     * @var bool whether the address can be an IP with CIDR subnet, like `192.168.10.0/24`.
     * The following values are possible:
     *
     * - `false` - the address must not have a subnet (default).
     * - `true` - specifying a subnet is required.
     * - `null` - specifying a subnet is optional.
     */
    public $subnet = false;
    /**
     * @var bool whether to add the CIDR prefix with the smallest length (32 for IPv4 and 128 for IPv6) to an
     * address without it. Works only when `subnet` is not `false`. For example:
     *  - `10.0.1.5` will normalized to `10.0.1.5/32`
     *  - `2008:db0::1` will be normalized to `2008:db0::1/128`
     *    Defaults to `false`.
     * @see subnet
     */
    public $normalize = false;
    /**
     * @var bool whether address may have a [[NEGATION_CHAR]] character at the beginning.
     * Defaults to `false`.
     */
    public $negation = false;
    /**
     * @var bool whether to expand an IPv6 address to the full notation format.
     * Defaults to `false`.
     */
    public $expandIPv6 = false;
    /**
     * @var string Regexp-pattern to validate IPv4 address
     */
    public $ipv4Pattern = '/^(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))$/';
    /**
     * @var string Regexp-pattern to validate IPv6 address
     */
    public $ipv6Pattern = '/^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/';
    /**
     * @var string user-defined error message is used when validation fails due to the wrong IP address format.
     *
     * You may use the following placeholders in the message:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     */
    public $message;
    /**
     * @var string user-defined error message is used when validation fails due to the disabled IPv6 validation.
     *
     * You may use the following placeholders in the message:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * @see ipv6
     */
    public $ipv6NotAllowed;
    /**
     * @var string user-defined error message is used when validation fails due to the disabled IPv4 validation.
     *
     * You may use the following placeholders in the message:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * @see ipv4
     */
    public $ipv4NotAllowed;
    /**
     * @var string user-defined error message is used when validation fails due to the wrong CIDR.
     *
     * You may use the following placeholders in the message:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     * @see subnet
     */
    public $wrongCidr;
    /**
     * @var string user-defined error message is used when validation fails due to subnet [[subnet]] set to 'only',
     * but the CIDR prefix is not set.
     *
     * You may use the following placeholders in the message:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * @see subnet
     */
    public $noSubnet;
    /**
     * @var string user-defined error message is used when validation fails
     * due to [[subnet]] is false, but CIDR prefix is present.
     *
     * You may use the following placeholders in the message:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * @see subnet
     */
    public $hasSubnet;
    /**
     * @var string user-defined error message is used when validation fails due to IP address
     * is not not allowed by [[ranges]] check.
     *
     * You may use the following placeholders in the message:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * @see ranges
     */
    public $notInRange;

    /**
     * @var array
     */
    private $_ranges = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!$this->ipv4 && !$this->ipv6) {
            throw new InvalidConfigException('Both IPv4 and IPv6 checks can not be disabled at the same time');
        }
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} must be a valid IP address.');
        }
        if ($this->ipv6NotAllowed === null) {
            $this->ipv6NotAllowed = Yii::t('yii', '{attribute} must not be an IPv6 address.');
        }
        if ($this->ipv4NotAllowed === null) {
            $this->ipv4NotAllowed = Yii::t('yii', '{attribute} must not be an IPv4 address.');
        }
        if ($this->wrongCidr === null) {
            $this->wrongCidr = Yii::t('yii', '{attribute} contains wrong subnet mask.');
        }
        if ($this->noSubnet === null) {
            $this->noSubnet = Yii::t('yii', '{attribute} must be an IP address with specified subnet.');
        }
        if ($this->hasSubnet === null) {
            $this->hasSubnet = Yii::t('yii', '{attribute} must not be a subnet.');
        }
        if ($this->notInRange === null) {
            $this->notInRange = Yii::t('yii', '{attribute} is not in the allowed range.');
        }
    }

    /**
     * Set the IPv4 or IPv6 ranges that are allowed or forbidden.
     *
     * The following preparation tasks are performed:
     *
     * - Recursively substitutes aliases (described in [[networks]]) with their values.
     * - Removes duplicates
     *
     * @property array the IPv4 or IPv6 ranges that are allowed or forbidden.
     * See [[setRanges()]] for detailed description.
     * @param array $ranges the IPv4 or IPv6 ranges that are allowed or forbidden.
     *
     * When the array is empty, or the option not set, all IP addresses are allowed.
     *
     * Otherwise, the rules are checked sequentially until the first match is found.
     * An IP address is forbidden, when it has not matched any of the rules.
     *
     * Example:
     *
     * ```php
     * [
     *      'ranges' => [
     *          '192.168.10.128'
     *          '!192.168.10.0/24',
     *          'any' // allows any other IP addresses
     *      ]
     * ]
     * ```
     *
     * In this example, access is allowed for all the IPv4 and IPv6 addresses excluding the `192.168.10.0/24` subnet.
     * IPv4 address `192.168.10.128` is also allowed, because it is listed before the restriction.
     */
    public function setRanges($ranges)
    {
        $this->_ranges = $this->prepareRanges((array) $ranges);
    }

    /**
     * @return array The IPv4 or IPv6 ranges that are allowed or forbidden.
     */
    public function getRanges()
    {
        return $this->_ranges;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $result = $this->validateSubnet($value);
        if (is_array($result)) {
            $result[1] = array_merge(['ip' => is_array($value) ? 'array()' : $value], $result[1]);
            return $result;
        }

        return null;
    }

    /**
     * {@inheritdoc}
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
     * Validates an IPv4/IPv6 address or subnet.
     *
     * @param $ip string
     * @return string|array
     * string - the validation was successful;
     * array  - an error occurred during the validation.
     * Array[0] contains the text of an error, array[1] contains values for the placeholders in the error message
     */
    private function validateSubnet($ip)
    {
        if (!is_string($ip)) {
            return [$this->message, []];
        }

        $negation = null;
        $cidr = null;
        $isCidrDefault = false;

        if (preg_match($this->getIpParsePattern(), $ip, $matches)) {
            $negation = ($matches[1] !== '') ? $matches[1] : null;
            $ip = $matches[2];
            $cidr = $matches[4] ?? null;
        }

        if ($this->subnet === true && $cidr === null) {
            return [$this->noSubnet, []];
        }
        if ($this->subnet === false && $cidr !== null) {
            return [$this->hasSubnet, []];
        }
        if ($this->negation === false && $negation !== null) {
            return [$this->message, []];
        }

        if ($this->getIpVersion($ip) === IpHelper::IPV6) {
            if ($cidr !== null) {
                if ($cidr > IpHelper::IPV6_ADDRESS_LENGTH || $cidr < 0) {
                    return [$this->wrongCidr, []];
                }
            } else {
                $isCidrDefault = true;
                $cidr = IpHelper::IPV6_ADDRESS_LENGTH;
            }

            if (!$this->validateIPv6($ip)) {
                return [$this->message, []];
            }
            if (!$this->ipv6) {
                return [$this->ipv6NotAllowed, []];
            }

            if ($this->expandIPv6) {
                $ip = $this->expandIPv6($ip);
            }
        } else {
            if ($cidr !== null) {
                if ($cidr > IpHelper::IPV4_ADDRESS_LENGTH || $cidr < 0) {
                    return [$this->wrongCidr, []];
                }
            } else {
                $isCidrDefault = true;
                $cidr = IpHelper::IPV4_ADDRESS_LENGTH;
            }
            if (!$this->validateIPv4($ip)) {
                return [$this->message, []];
            }
            if (!$this->ipv4) {
                return [$this->ipv4NotAllowed, []];
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
     * Expands an IPv6 address to it's full notation.
     *
     * For example `2001:db8::1` will be expanded to `2001:0db8:0000:0000:0000:0000:0000:0001`.
     *
     * @param string $ip the original IPv6
     * @return string the expanded IPv6
     */
    private function expandIPv6($ip)
    {
        return IpHelper::expandIPv6($ip);
    }

    /**
     * The method checks whether the IP address with specified CIDR is allowed according to the [[ranges]] list.
     *
     * @param string $ip
     * @param int $cidr
     * @return bool
     * @see ranges
     */
    private function isAllowed($ip, $cidr)
    {
        if (empty($this->ranges)) {
            return true;
        }

        foreach ($this->ranges as $string) {
            [$isNegated, $range] = $this->parseNegatedRange($string);
            if ($this->inRange($ip, $cidr, $range)) {
                return !$isNegated;
            }
        }

        return false;
    }

    /**
     * Parses IP address/range for the negation with [[NEGATION_CHAR]].
     *
     * @param $string
     * @return array `[0 => bool, 1 => string]`
     *  - boolean: whether the string is negated
     *  - string: the string without negation (when the negation were present)
     */
    private function parseNegatedRange($string)
    {
        $isNegated = strpos($string, static::NEGATION_CHAR) === 0;
        return [$isNegated, $isNegated ? substr($string, strlen(static::NEGATION_CHAR)) : $string];
    }

    /**
     * Prepares array to fill in [[ranges]].
     *
     *  - Recursively substitutes aliases, described in [[networks]] with their values,
     *  - Removes duplicates.
     *
     * @param $ranges
     * @return array
     * @see networks
     */
    private function prepareRanges($ranges)
    {
        $result = [];
        foreach ($ranges as $string) {
            [$isRangeNegated, $range] = $this->parseNegatedRange($string);
            if (isset($this->networks[$range])) {
                $replacements = $this->prepareRanges($this->networks[$range]);
                foreach ($replacements as &$replacement) {
                    [$isReplacementNegated, $replacement] = $this->parseNegatedRange($replacement);
                    $result[] = ($isRangeNegated && !$isReplacementNegated ? static::NEGATION_CHAR : '') . $replacement;
                }
            } else {
                $result[] = $string;
            }
        }

        return array_unique($result);
    }

    /**
     * Validates IPv4 address.
     *
     * @param string $value
     * @return bool
     */
    protected function validateIPv4($value)
    {
        return preg_match($this->ipv4Pattern, $value) !== 0;
    }

    /**
     * Validates IPv6 address.
     *
     * @param string $value
     * @return bool
     */
    protected function validateIPv6($value)
    {
        return preg_match($this->ipv6Pattern, $value) !== 0;
    }

    /**
     * Gets the IP version.
     *
     * @param string $ip
     * @return int
     */
    private function getIpVersion($ip)
    {
        return IpHelper::getIpVersion($ip);
    }

    /**
     * Used to get the Regexp pattern for initial IP address parsing.
     * @return string
     */
    public function getIpParsePattern()
    {
        return '/^(' . preg_quote(static::NEGATION_CHAR, '/') . '?)(.+?)(\/(\d+))?$/';
    }

    /**
     * Checks whether the IP is in subnet range.
     *
     * @param string $ip an IPv4 or IPv6 address
     * @param int $cidr
     * @param string $range subnet in CIDR format e.g. `10.0.0.0/8` or `2001:af::/64`
     * @return bool
     */
    private function inRange($ip, $cidr, $range)
    {
        return IpHelper::inRange($ip . '/' . $cidr, $range);
    }
}
