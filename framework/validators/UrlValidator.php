<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * UrlValidator 校验指定的属性值是否是一个合法的 http 或者 https URL。
 *
 * 注意：这个校验器只确保 URL 的协议和主机名部分是否正确，
 * 它并不会校验其余部分。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlValidator extends Validator
{
    /**
     * @var string 校验属性值的正则表达式。
     * 模式包含一个 `{schemes}` 占位，
     * 这个占位将会被一个代表 [[validSchemes]] 的正则表达式替换。
     */
    public $pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i';
    /**
     * @var array 合法的 URI 协议列表。
     * 默认 http 和 https 是合法的协议。
     */
    public $validSchemes = ['http', 'https'];
    /**
     * @var string 默认的 URI 协议。如果输入没有包含协议部分，
     * 默认的协议会被插入前面（即会改变输入）。
     * 默认为 null ，意味着 URL 必须包含协议部分。
     */
    public $defaultScheme;
    /**
     * @var bool 是否校验过程启用 IDN (国际化域名名称）。
     * 默认为 false 意味着校验包含 IDN 的 URLs 将会失败。
     * 注意为了使用 IDN 校验，
     * 你需要安装和启用 PHP 扩展 `intl` 否则将会抛出异常。
     */
    public $enableIDN = false;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->enableIDN && !function_exists('idn_to_ascii')) {
            throw new InvalidConfigException('In order to use IDN validation intl extension must be installed and enabled.');
        }
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is not a valid URL.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $result = $this->validateValue($value);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        } elseif ($this->defaultScheme !== null && strpos($value, '://') === false) {
            $model->$attribute = $this->defaultScheme . '://' . $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        // make sure the length is limited to avoid DOS attacks
        if (is_string($value) && strlen($value) < 2000) {
            if ($this->defaultScheme !== null && strpos($value, '://') === false) {
                $value = $this->defaultScheme . '://' . $value;
            }

            if (strpos($this->pattern, '{schemes}') !== false) {
                $pattern = str_replace('{schemes}', '(' . implode('|', $this->validSchemes) . ')', $this->pattern);
            } else {
                $pattern = $this->pattern;
            }

            if ($this->enableIDN) {
                $value = preg_replace_callback('/:\/\/([^\/]+)/', function ($matches) {
                    return '://' . $this->idnToAscii($matches[1]);
                }, $value);
            }

            if (preg_match($pattern, $value)) {
                return null;
            }
        }

        return [$this->message, []];
    }

    private function idnToAscii($idn)
    {
        if (PHP_VERSION_ID < 50600) {
            // TODO: drop old PHP versions support
            return idn_to_ascii($idn);
        }

        return idn_to_ascii($idn, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        if ($this->enableIDN) {
            PunycodeAsset::register($view);
        }
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.url(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        if (strpos($this->pattern, '{schemes}') !== false) {
            $pattern = str_replace('{schemes}', '(' . implode('|', $this->validSchemes) . ')', $this->pattern);
        } else {
            $pattern = $this->pattern;
        }

        $options = [
            'pattern' => new JsExpression($pattern),
            'message' => $this->formatMessage($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ]),
            'enableIDN' => (bool) $this->enableIDN,
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        if ($this->defaultScheme !== null) {
            $options['defaultScheme'] = $this->defaultScheme;
        }

        return $options;
    }
}
