<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Locale 提供一些实用的方法获取各种区域信息。
 *
 * 使用这个类需要安装 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php)。
 *
 * @property string $currencySymbol 此属性是只读的。
 *
 * @since 2.0.14
 */
class Locale extends Component
{
    /**
     * @var string the locale ID.
     * 如果没有设置，将使用 [[\yii\base\Application::language]]。
     */
    public $locale;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!extension_loaded('intl')) {
            throw new InvalidConfigException('Locale component requires PHP intl extension to be installed.');
        }

        if ($this->locale === null) {
            $this->locale = Yii::$app->language;
        }
    }

    /**
     * 返回货币符号
     *
     * @param string $currencyCode 货币代码 ISO 4217 中的三个字母的字母代码。
     * 如果为 null，则方法将尝试使用 [[locale]] 中的货币代码。
     * @return string
     */
    public function getCurrencySymbol($currencyCode = null)
    {
        $locale = $this->locale;

        if ($currencyCode !== null) {
            $locale .= '@currency=' . $currencyCode;
        }

        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
    }
}
