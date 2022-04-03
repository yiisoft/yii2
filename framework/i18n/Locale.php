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
 * Locale provides various locale information via convenient methods.
 *
 * The class requires [PHP intl extension](https://www.php.net/manual/en/book.intl.php) to be installed.
 *
 * @property-read string $currencySymbol
 *
 * @since 2.0.14
 */
class Locale extends Component
{
    /**
     * @var string|null the locale ID.
     * If not set, [[\yii\base\Application::language]] will be used.
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
     * Returns a currency symbol
     *
     * @param string|null $currencyCode the 3-letter ISO 4217 currency code to get symbol for. If null,
     * method will attempt using currency code from [[locale]].
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
