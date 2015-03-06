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
     * @var string the user-defined error message.
     */
    public $message;

    /**
     * @var boolean whether support of IPv6 addresses is enabled
     */
    public $ipv6 = true;

    /**
     * @var boolean whether support of IPv4 addresses is enabled
     */
    public $ipv4 = true;

    /**
     * @var boolean|string whether address may be a subnet
     *     boolean - normal behaviour
     *     string  - value 'only' to validate only address with a prefix
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
     * @var boolean whether to allow IP address from private
     * networks e.g. 192.168.0.0/16, 10.0.0.0/8, 172.16.0.0/12.
     * For IPv6 will fail validation for addresses starting with FD or FC.
     */
    public $private = true;

    /**
     * @var string Regexp-pattern to validate IPv4 address
     */
    public $ipv4Pattern = '/^(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))$/';

    /**
     * @var string Regexp-pattern to validate IPv6 address
     */
    public $ipv6Pattern = '/^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/';


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
            return [$this->message, []];
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
            $this->addError($model, $attribute, $this->message);
        } else {
            $model->$attribute = $result;
        }
    }

    /**
     * Validates an IPv4/IPv6 address or subnet
     * @param $ip string
     * @return bool|string
     */
    public function validateSubnet($ip)
    {
        if (!is_string($ip)) {
            return false;
        }

        $exclude = null;
        $prefix = null;

        $exclude_character = is_string($this->exclude) ? preg_quote($this->exclude, '/') : '!';
        if (preg_match("/^($exclude_character?)(.+?)(\/(\d+))?$/", $ip, $matches)) {
            $exclude = ($matches[1] !== '') ? $matches[1] : null;
            $ip = $matches[2];
            $prefix = isset($matches[4]) ? $matches[4] : null;
        }

        if ($this->subnet === 'only' && $prefix == null) {
            return false;
        }
        if (!$this->subnet && $prefix !== null) {
            return false;
        }
        if ($this->exclude === false && $exclude !== null) {
            return false;
        }

        if (strpos($ip, ':') !== false) {
            if ($prefix !== null) {
                if ($prefix > 128 || $prefix < 0) {
                    return false;
                }
            } elseif ($this->normalize) {
                $prefix = 128;
            }

            if (!$this->validate6($ip)) {
                return false;
            }

            if ($this->expandV6) {
                $hex = unpack("H*hex", inet_pton($ip));
                $ip = substr(preg_replace("/([a-f0-9]{4})/i", "$1:", $hex['hex']), 0, -1);
            }
        } else {
            if ($prefix !== null) {
                if ($prefix > 32 || $prefix < 0) {
                    return false;
                }
            } elseif ($this->normalize) {
                $prefix = 32;
            }

            if (!$this->validate4($ip)) {
                return false;
            }
        }

        $result = $exclude . $ip;

        if ($this->subnet) {
            $result .= "/$prefix";
        }

        return $result;
    }

    /**
     * Validates IPv4 address
     * @param $value string
     * @return boolean
     */
    public function validate4($value)
    {
        if (!$this->ipv4) {
            return false;
        }

        return preg_match($this->ipv4Pattern, $value) !== 0;
    }

    /**
     * Validates IPv6 address
     * @param $value string
     * @return boolean
     */
    public function validate6($value)
    {
        if (!$this->ipv6) {
            return false;
        }

        return preg_match($this->ipv6Pattern, $value) !== 0;
    }
}
