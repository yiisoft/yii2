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
use yii\helpers\IpHelper;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * 这个校验器校验属性值是否是一个合法的 IPv4/IPv6 地址或者子网。
 *
 * 如果启用 IPv6 扩展标准化，它同样会改变属性值。
 *
 * 如下是使用这个校验器的校验规则示例：
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
     * 否定操作符。
     *
     * 用于否定 [[ranges]] 或者 [[networks]] 或者 当 [[negation]] 设置为 `true` 时，否定校验的值。
     * @see negation
     * @see networks
     * @see ranges
     */
    const NEGATION_CHAR = '!';

    /**
     * @var array 网络别名，这个可以被用于 [[ranges]] 中。
     * - key - 别名名称
     * - value - 数组字符串。字符串可以是 IP 范围，IP 地址 或者其他别名。
     *   字符串可以用 [[NEGATION_CHAR]] 取反（和 `negation` 属性独立）
     *
     * 以下是预定义的别名：
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
     * @var bool 待校验的值是否可以是 IPv6 地址。默认为 `true`。
     */
    public $ipv6 = true;
    /**
     * @var bool 待校验的值是否可以是 IPv4 地址。默认为 `true`。
     */
    public $ipv4 = true;
    /**
     * @var bool 地址是否可以为一个包含 CIDR 子网的 IP，例如 `192.168.10.0/24`。
     * 可能是以下的值之一：
     *
     * - `false` - 地址不能包含子网（默认）。
     * - `true` - 地址必须包含子网。
     * - `null` - 地址不必须包含子网。
     */
    public $subnet = false;
    /**
     * @var bool 当地址没有后缀时，是否默认添加最小长度的 CIDR 后缀（ IPv4 为32，IPv6 为128）。
     * 只有 `subnet` 不为 `false` 时才有效。例如：
     *  - `10.0.1.5` will normalized to `10.0.1.5/32`
     *  - `2008:db0::1` will be normalized to `2008:db0::1/128`
     *    Defaults to `false`.
     * @see subnet
     */
    public $normalize = false;
    /**
     * @var bool 地址是否可以包含 [[NEGATION_CHAR]] 在开头处。
     * 默认为 `false`。
     */
    public $negation = false;
    /**
     * @var bool 是否将 IPv6 扩展为完整格式。
     * 默认为 `false`。
     */
    public $expandIPv6 = false;
    /**
     * @var string 用于校验 IPv4 地址的正则表达式
     */
    public $ipv4Pattern = '/^(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))$/';
    /**
     * @var string 用于校验 IPv6 地址的正则表达式
     */
    public $ipv6Pattern = '/^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/';
    /**
     * @var string 用户自定义错误消息用于校验遇到错误的 IP 地址格式时。
     *
     * 你可以在消息中使用如下的占位符：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
     */
    public $message;
    /**
     * @var string 用户自定义错误消息用于禁用 IPv6 校验时遇到 IPv6 地址。
     *
     * 你可以在消息中使用如下的占位符：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
     *
     * @see ipv6
     */
    public $ipv6NotAllowed;
    /**
     * @var string 用户自定义错误消息用于禁用 IPv4 校验时遇到 IPv4 地址。
     *
     * 你可以在消息中使用如下的占位符：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
     *
     * @see ipv4
     */
    public $ipv4NotAllowed;
    /**
     * @var string 用户自定义错误消息用于因 CIDR 产生时。
     *
     * 你可以在消息中使用如下的占位符：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
     * @see subnet
     */
    public $wrongCidr;
    /**
     * @var string 用户自定义错误消息
     * 当 [[subnet]] 设置为 'only'，但是 CIDR 后缀没有设置时，校验失败。
     *
     * 你可以在消息中使用如下的占位符：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
     *
     * @see subnet
     */
    public $noSubnet;
    /**
     * @var string 用户自定义错误消息，
     * 当 [[subnet]] 为 false 时，但提供了 CIDR 后缀导致校验失败。
     *
     * 你可以在消息中使用如下的占位符：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
     *
     * @see subnet
     */
    public $hasSubnet;
    /**
     * @var string 用户自定义错误消息，
     * 当 IP 地址不在 [[ranges]] 允许的范围。
     *
     * 你可以在消息中使用如下的占位符：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
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
     * 设置允许或者禁止的 IPv4 或者 IPv6 范围。
     *
     * 会执行以下预处理过程：
     *
     * - 递归的将别名用其值替换（ 在 [[networks]] 中定义）
     * - 移除重复值
     *
     * @property array 允许或者禁止的 IPv4 或者 IPv6 范围.
     * 更多描述参考 [[setRanges()]]
     * @param array $ranges 允许或者禁止的 IPv4 或者 IPv6 范围.
     *
     * 如果数组是空的，或者属性未涉足，所以的 IP 地址将被允许。
     *
     * 其他情况，这些规则将被依次执行直到找到第一条匹配的。
     * 当一个 IP 地址没有被任意规则匹配时，它将被禁止。
     *
     * 例如:
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
     * 在这个例子中，所有的 IPv4 和 IPv6 地址都被允许，除了子网 `192.168.10.0/24`。
     * IPv4 地址 `192.168.10.128` 将被允许，因为它列在限制前面。
     */
    public function setRanges($ranges)
    {
        $this->_ranges = $this->prepareRanges((array) $ranges);
    }

    /**
     * @return array 允许或者禁止的 IPv4 或者 IPv6 地址
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
     * 校验一个 IPv4/IPv6 地址或者子网。
     *
     * @param $ip string
     * @return string|array
     * string - 校验成功时；
     * array  - 校验过程中的错误
     * Array[0] 包含错误消息， array[1] 包含错误消息中占位符替换所需要的值
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
            $cidr = isset($matches[4]) ? $matches[4] : null;
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
     * 将 IPv6 扩展成它的完整格式。
     *
     * 例如 `2001:db8::1` 将会被扩展成 `2001:0db8:0000:0000:0000:0000:0000:0001`。
     *
     * @param string $ip 原始 IPv6
     * @return string 扩展后的 IPv6
     */
    private function expandIPv6($ip)
    {
        return IpHelper::expandIPv6($ip);
    }

    /**
     * 这个方法根据 [[ranges]] 列表检查 IP 和指定的 CIDR 是否被允许。
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
            list($isNegated, $range) = $this->parseNegatedRange($string);
            if ($this->inRange($ip, $cidr, $range)) {
                return !$isNegated;
            }
        }

        return false;
    }

    /**
     * 根据否定操作符 [[NEGATION_CHAR]] 解析 IP 地址/范围。
     *
     * @param $string
     * @return array `[0 => bool, 1 => string]`
     *  - boolean: 是否对地址字符串取反
     *  - string: 未取反的地址字符串（取反操作在前）
     */
    private function parseNegatedRange($string)
    {
        $isNegated = strpos($string, static::NEGATION_CHAR) === 0;
        return [$isNegated, $isNegated ? substr($string, strlen(static::NEGATION_CHAR)) : $string];
    }

    /**
     * 准备一个数组来填充 [[ranges]]。
     *
     *  - 递归的将别名替换，其值在 [[networks]] 中定义
     *  - 移除重复值
     *
     * @param $ranges
     * @return array
     * @see networks
     */
    private function prepareRanges($ranges)
    {
        $result = [];
        foreach ($ranges as $string) {
            list($isRangeNegated, $range) = $this->parseNegatedRange($string);
            if (isset($this->networks[$range])) {
                $replacements = $this->prepareRanges($this->networks[$range]);
                foreach ($replacements as &$replacement) {
                    list($isReplacementNegated, $replacement) = $this->parseNegatedRange($replacement);
                    $result[] = ($isRangeNegated && !$isReplacementNegated ? static::NEGATION_CHAR : '') . $replacement;
                }
            } else {
                $result[] = $string;
            }
        }

        return array_unique($result);
    }

    /**
     * 校验 IPv4 地址。
     *
     * @param string $value
     * @return bool
     */
    protected function validateIPv4($value)
    {
        return preg_match($this->ipv4Pattern, $value) !== 0;
    }

    /**
     * 校验 IPv6 地址。
     *
     * @param string $value
     * @return bool
     */
    protected function validateIPv6($value)
    {
        return preg_match($this->ipv6Pattern, $value) !== 0;
    }

    /**
     * 获得 IP 版本。
     *
     * @param string $ip
     * @return int
     */
    private function getIpVersion($ip)
    {
        return IpHelper::getIpVersion($ip);
    }

    /**
     * 用于获得正则表达式以初始化 IP 地址解析
     * @return string
     */
    private function getIpParsePattern()
    {
        return '/^(' . preg_quote(static::NEGATION_CHAR, '/') . '?)(.+?)(\/(\d+))?$/';
    }

    /**
     * 检查 IP 是否在子网范围内。
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

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.ip(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $messages = [
            'ipv6NotAllowed' => $this->ipv6NotAllowed,
            'ipv4NotAllowed' => $this->ipv4NotAllowed,
            'message' => $this->message,
            'noSubnet' => $this->noSubnet,
            'hasSubnet' => $this->hasSubnet,
        ];
        foreach ($messages as &$message) {
            $message = $this->formatMessage($message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ]);
        }

        $options = [
            'ipv4Pattern' => new JsExpression(Html::escapeJsRegularExpression($this->ipv4Pattern)),
            'ipv6Pattern' => new JsExpression(Html::escapeJsRegularExpression($this->ipv6Pattern)),
            'messages' => $messages,
            'ipv4' => (bool) $this->ipv4,
            'ipv6' => (bool) $this->ipv6,
            'ipParsePattern' => new JsExpression(Html::escapeJsRegularExpression($this->getIpParsePattern())),
            'negation' => $this->negation,
            'subnet' => $this->subnet,
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
