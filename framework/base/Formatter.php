<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use DateTime;
use IntlDateFormatter;
use NumberFormatter;
use yii\helpers\HtmlPurifier;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\i18n\FormatDefs;


/**
 * Formatter provides a set of commonly used data formatting methods. 
 * 
 * The formatting methods provided by Formatter are all named in the form of `asXyz()`.
 * The behavior of some of them may be configured via the properties of Formatter. For example,
 * by configuring [[dateFormat]], one may control how [[asDate()]] formats the value into a date string.
 *
 * Formatter is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->formatter`.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * 
 * Refactoring of formatter class by
 * @author Enrica Ruedin <e.ruedin@guggach.com>
 * 
 * Original version of Yii2 has two formatter classes in "yii\base\formatter" and "yii\i18n\formatter". Fist uses PHP 
 * to handle date formats with php format patterns like "Y-m-d" -> "2014-06-02" while second uses icu format 
 * patterns from php extension "intl" like "yyyy-mm-dd" -> "2014-06-02". Further icu knows terms like "short", "medium", 
 * "long" and "full" which holds predefined patterns which are missing in yii\base formatter.
 * 
 * I have seen an extension which uses yii::$app->formatter->format($value, ['date', 'Y-m-d']). This will crash
 * if a developper uses yii\i18n formatter because intl doesn't know this format pattern.
 * 
 * This refactored formatter version combines localized i18n with base functions. If "intl" extension is installed
 * ICU standard is used internally. If "intl" want to be used or can't be loaded most functionality is simulated with php.
 * A separate definiton class in 'yii\i18n\FormatDefs.php' has an array with localized format defintions. 
 * As a constraint month and day names are in english only.
 * 
 * The communication with formatter class is per standard with php format patterns. They are converted internally to
 * icu format patterns. Further it supports for date, time and datetime the named patterns "short", "medium", "long" and
 * "full" plus "db" (database), also if "intl" isn't loaded. The format function has an option parameter to use "icu"
 * format patterns.
 * 
 * All number fomatters of yii\i18n\ are merged with yii\base in this formatter. Formatted numbers aren't readable for
 * a machine as numeric. Therefore an "unformat" function for all "format" types has been built.
 *  
 * Databases need the iso format for date, time and datetime normally. (eg. 2014-06-02 14:53:02) The dbFormat can be
 * configured in the component section also.
 * 
 * For currency amounts the currency code is taken from "intl" (if loaded). Otherwise it can be defined in a localizing
 * array (formatterIntl). The rounding rule can be defined in config with "$roundingIncrement". For Swiss Francs formatter rounds
 * automatically to 5 cents.
 * 
 *  */
class Formatter extends yii\base\Component
{
    
    
    /**
     * @var string the locale ID that is used to localize the date and number formatting.
     * If not set, [[\yii\base\Application::language]] will be used.
     */
    public $locale;
/**
     * @var string the timezone to use for formatting time and date values.
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     */
    public $timeZone;
    /**
     * @var string the default format string to be used to format a date using PHP date() function.
     * Possible values are: "short", "medium", "long", "full" as predifined date formats like in ICU or
     * format pattern in php format. NOT ICU format!
     * 
     * After initialization of object the named predifined format will be replaced by the corresponding
     * php format string.
     */
    public $dateFormat = 'medium'; // php:'Y-m-d';
    /**
     * @var string the default format string to be used to format a time using PHP date() function.
     * see "$dateFormat"
     */
    public $timeFormat = 'medium'; // php: 'H:i:s';
    /**
     * @var string the default format string to be used to format a date and time using PHP date() function.
     * see "$dateFormat"
     */
    public $datetimeFormat = 'medium';  // php: 'Y-m-d H:i:s';
    /**
     *
     * @var string default format pattern for database requested format. 
     */
    public $dbFormat = ['date' => 'Y-m-d','time' => 'H:i:s', 'dbtimeshort'=>'H:i' ,'datetime' => 'Y-m-d H:i:s', 'dbdatetimeshort' => 'Y-m-d H:i'];
    /**
     * @var string the text to be displayed when formatting a null. Defaults to '<span class="not-set">(not set)</span>'.
     */
    public $nullDisplay;
    /**
     * @var array the text to be displayed when formatting a boolean value. The first element corresponds
     * to the text display for false, the second element for true. Defaults to `['No', 'Yes']`.
     */
    public $booleanFormat;
    /**
     * @var array the options to be set for the NumberFormatter objects (eg. grouping used). Please refer to
     * [PHP manual](http://php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatattribute)
     * for the possible options. This property is used by [[createNumberFormatter]] when
     * creating a new number formatter to format decimals, currencies, etc.
     */
    public $numberFormatOptions = [];
    /**
     * @var array the text options to be set for the NumberFormatter objects (eg. Negative sign). Please refer to
     * [PHP manual](http://php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatattribute)
     * for the possible options. This property is used by [[createNumberFormatter]] when
     * creating a new number formatter to format decimals, currencies, etc.
     * 
     * Default value: GOUPING_USED = 1 / MAX_FRACTION_DIGITS = 3 / GROUPING_SIZE = 3 / ROUNDING_MODE = 4
     */
    public $numberTextFormartOptions = [];
    /**
     * @var string the character displayed as the decimal point when formatting a number.
     * If not set, "." will be used.
     */
    public $decimalSeparator;
    /**
     * @var string the character displayed as the thousands separator character when formatting a number.
     * If not set, "," will be used.
     */
    public $thousandSeparator;
    /**
     *
     * @var string: Standard currency code for currency formatting. With "intl" library not usefull
     * because "intl" uses the local currency code by default. There with "intl" it should null.
     * Without "intl" the currency code can be defined in array in position 14 per locale code. 
     * With this var a standard code can be defined in config file.
     */
    public $currencyCode;
    /**
     *
     * @var type float "intl" numberformat library knows a rounding increment 
     * This means that any value is rounded to this increment.
     * Example: increment of 0.05 rounds values <= 2.024 to 2.00 / values >= 2.025 to 2.05
     */
    public $roundingIncrement;
    public $roundingIncrCurrency;
    /**
     * @var array the format used to format size (bytes). Three elements may be specified: "base", "decimals" and "decimalSeparator".
     * They correspond to the base at which a kilobyte is calculated (1000 or 1024 bytes per kilobyte, defaults to 1024),
     * the number of digits after the decimal point (defaults to 2) and the character displayed as the decimal point. 
     */
    public $sizeFormat = [
        'base' => 1024,
        'decimals' => 2,
        'decimalSeparator' => null,
    ];

    /**
     * @var boolean shows if the php extension is loaded
     * If intl is loaded the icu format and intDateFormatter is used
     */
    private $_intlLoaded = false;

    /**
     * @var private strings hold the format patterns for date, time and
     * dattime in ICU format. ICU format is used internally only.
     *
     */
    private $_dateFormatIcu;
    private $_timeFormatIcu;
    private $_datetimeFormatIcu;
    
    // IntlDateFormatter can't be used here because there will be an error
    // if intl extension isn't loaded.
    private $_dateFormatsIcu = [
        'short' =>  3,   // IntlDateFormatter::SHORT,
        'medium' => 2,   // IntlDateFormatter::MEDIUM,
        'long' =>   1,   // IntlDateFormatter::LONG,
        'full' =>   0,   // IntlDateFormatter::FULL,
    ];
    
    /**
     *
     * @var type array with the standard php definition for short, medium, long an full
     * format as pattern for date, time and datetime.
     * The number behind pattern is the array index of localized formatterIntl array
     * for same combination like [short][date][1] = 2
     */
    private $_PhpNameToPattern = [
                        'short' => [
                            'date' => ['y-m-d', 2],
                            'time' => ['H:i', 6],
                            'datetime' => ['y-m-d H:i', 10],
                            ],
                        'medium' => [
                            'date' => ['Y-m-d', 3],
                            'time' => ['H:i:s', 7],
                            'datetime' => ['Y-m-d H:i:s', 11]
                            ],
                        'long' => [
                            'date' => ['F j, Y', 4],
                            'time' => ['g:i:sA', 8],
                            'datetime' => ['F j, Y g:i:sA', 12]
                            ],
                        'full' => [
                            'date' => ['l, F j, Y', 5],
                            'time' => ['g:i:sA T', 9],
                            'datetime' => ['l, F j, Y g:i:sA T', 13]
                            ],
                        ];
    
    /**
     *
     * @var type arry: stores the originally configured values for dateFormat,
     * timeFormat and datetimeFormat, because the variables values will be replaced 
     * by the format pattern during initialization.
     */
    private $_originalConfig = [];
    
    /**
     * Initializes the component.
     */
    public function init()
    {
        if ($this->timeZone === null) {
            $this->timeZone = Yii::$app->timeZone;
        }
        
        if ($this->locale === null) {
            $this->locale = Yii::$app->language;
        }

        if (empty($this->booleanFormat)) {
            $this->booleanFormat = [Yii::t('yii', 'No'), Yii::t('yii', 'Yes')];
        }
        if ($this->nullDisplay === null) {
            $this->nullDisplay = '<span class="not-set">' . Yii::t('yii', '(not set)') . '</span>';
        }
        
        if (extension_loaded('intl')) {
            $this->_intlLoaded = true;
            $this->numberFormatOptions= [NumberFormatter::ROUNDING_MODE => NumberFormatter::ROUND_HALFUP];
        }
        
        $this->_originalConfig['date'] = $this->dateFormat;
        $this->setFormatPattern($this->dateFormat, 'date');
            
        $this->_originalConfig['time'] = $this->timeFormat;
        $this->setFormatPattern($this->timeFormat, 'time');
            
        $this->_originalConfig['datetime'] = $this->datetimeFormat;
        $this->setFormatPattern($this->datetimeFormat, 'datetime');
        
        $this->setDecimalSeparator($this->decimalSeparator);
        $this->setThousandSeparator($this->thousandSeparator);
        
        if (preg_match('/\bde-CH\b|\bfr-CH\b|\bit-CH\b/', $this->locale)){
            // Swiss currency amounts must be rounded to 0.05 (5-Rappen) instead of
            // 0.01 as usual
            $this->roundingIncrCurrency = '0.05';
        }
        
    }

     /**
     * Formats the value based on the given format type.
     * This method will call one of the "as" methods available in this class to do the formatting.
     * For type "xyz", the method "asXyz" will be used. For example, if the format is "html",
     * then [[asHtml()]] will be used. Format names are case insensitive.
     * @param mixed $value the value to be formatted
     * @param string|array $format the format of the value, e.g., "html", "text". To specify additional
     * parameters of the formatting method, you may use an array. The first element of the array
     * specifies the format name, while the rest of the elements will be used as the parameters to the formatting
     * method. For example, a format of `['date', 'Y-m-d', 'php']` will cause the invocation of `asDate($value, 'Y-m-d', 'php')`.
     * For more details see asXXX functions.
     * @return string the formatting result
     * @throws InvalidParamException if the type is not supported by this class.
     */
    public function format($value, $format)
    {
        if (is_array($format)) {
            if (!isset($format[0])) {
                throw new InvalidParamException('The $format array must contain at least one element.');
            }
            $f = $format[0];
            $format[0] = $value;
            $params = $format;
            $format = $f;
        } else {
            $params = [$value];
        }
        $method = 'as' . $format;
        if ($this->hasMethod($method)) {
            return call_user_func_array([$this, $method], $params);
        } else {
            throw new InvalidParamException("Unknown type: $format");
        }
    }

     /**
     * Unformats a formatted value based on the given format type to a machine readable value.
     *
     * This method will call one of the "as" methods available in this class to do the formatting.
     * For type "xyz", the method "ufXyz" will be used. For example, if the format is "double",
     * then [[ufDouble()]] will be used. Format names are case insensitive.
     * @param mixed $value the value to be unformatted
     * @param string|array $format the format of the value, e.g., "double", "currency". To specify additional
     * parameters of the unformatting method, you may use an array. The first element of the array
     * specifies the format name, while the rest of the elements will be used as the parameters to the formatting
     * method. For example, a format of `['date', 'Y-m-d', 'php']` will cause the invocation of `ufDate($value, 'Y-m-d', 'php')`.
     * For more details see ufXXX functions.
     * @return string the formatting result
     * @throws InvalidParamException if the type is not supported by this class.
     */
    public function unformat($value, $format)
    {
        if (is_array($format)) {
            if (!isset($format[0])) {
                throw new InvalidParamException('The $format array must contain at least one element.');
            }
            $f = $format[0];
            $format[0] = $value;
            $params = $format;
            $format = $f;
        } else {
            $params = [$value];
        }
        $method = 'uf' . $format;
        if ($this->hasMethod($method)) {
            return call_user_func_array([$this, $method], $params);
        } else {
            throw new InvalidParamException("Unknown type: $format");
        }
    }
        
     /**
     * intlFormatter class (ICU based) and DateTime class don't have same format string.
     * These format patterns are completely incompatible and must be converted.
     * 
     * This method converts an ICU (php intl) formatted date, time or datetime string in 
     * a php compatible format string.
     * 
     * @param type string $pattern: dateformat pattern like 'dd.mm.yyyy' or 'short'/'medium'/
     *          'long'/'full' or 'db
     * @param type string $type: if pattern has a name like 'short', type must define if
     * a date, time or datetime string should be formatted.
     * @return type string with converted date format pattern.
     * @throws InvalidConfigException
     */
    public function convertPatternIcuToPhp($pattern, $type = 'date') {
        if (preg_match('/\bshort\b|\bmedium\b|\blong\b|\bfull\b/', strtolower($pattern))){
            if ($this->_intlLoaded){
                switch (strtolower($type)){
                    case 'date':
                        $formatter = new IntlDateFormatter($this->locale, $this->_dateFormatsIcu[$pattern], IntlDateFormatter::NONE, $this->timeZone);
                        break;
                    case 'time':
                        $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, $this->_dateFormatsIcu[$pattern], $this->timeZone);
                        break;
                    case 'datetime':
                        $formatter = new IntlDateFormatter($this->locale, $this->_dateFormatsIcu[$pattern], $this->_dateFormatsIcu[$pattern], $this->timeZone);
                        break;
                        
                    default:
                        throw new InvalidConfigException('Conversion of ICU to PHP with a not supported type [date, time, datetime].');
                }
                $pattern = $formatter->getPattern();
            }    
            else {
//                throw new InvalidConfigException('ICU pattern "short", "medium", "long" and "full" can\'t be used if intl extension isn\'t loaded.');
                $localArr = FormatDefs::definition($this->locale);
                if (isset($localArr[0])){
                    return $localArr[$this->_PhpNameToPattern[$pattern][$type][1]];
                } else {
                    return $this->_PhpNameToPattern[strtolower($pattern)][$type][0];
                    // _PhpNameToPattern['short']['date'] --> 'y-m-d'
                }
            }
        } elseif (strtolower($pattern) === 'db'){
            return $this->dbFormat[strtolower($type)];
        }
        
        return strtr($pattern, [
            'dd' => 'd',    // day with leading zeros
            'd' => 'j',     // day without leading zeros
            'E' => 'D',     // day written in short form eg. Sun
            'EE' => 'D',
            'EEE' => 'D',
            'EEEE' => 'l',  // day fully written eg. Sunday
            'e' => 'N',     // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
            'ee' => 'N',    // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
                            // engl. ordinal st, nd, rd; it's not support by ICU but we added
            'D' => 'z',     // day of the year 0 to 365
            'w' => 'W',     // ISO-8601 week number of year, weeks starting on Monday
            'W' => '',      // week of the current month; isn't supported by php
            'F' => '',      // Day of Week in Month. eg. 2nd Wednesday in July
            'g' => '',      // Modified Julian day. This is different from the conventional Julian day number in two regards. 
            'M' => 'n',     // Numeric representation of a month, without leading zeros
            'MM' => 'm',    // Numeric representation of a month, with leading zeros
            'MMM' => 'M',   // A short textual representation of a month, three letters
            'MMMM' => 'F',  // A full textual representation of a month, such as January or March
            'Q' => '',      // number of quarter not supported in php
            'QQ' => '',     // number of quarter '02' not supported in php
            'QQQ' => '',    // quarter 'Q2' not supported in php
            'QQQQ' => '',   // quarter '2nd quarter' not supported in php
            'QQQQQ' => '',  // number of quarter '2' not supported in php
            'Y' => 'Y',     // 4digit year number eg. 2014
            'y' => 'Y',     // 4digit year also
            'yyyy' => 'Y',  // 4digit year also
            'yy' => 'y',    // 2digit year number eg. 14
            'r' => '',      // related Gregorian year, not supported by php
            'G' => '',      // ear designator like AD
            'a' => 'a',     // Lowercase Ante meridiem and Post 
            'h' => 'g',     // 12-hour format of an hour without leading zeros 1 to 12h
            'K' => 'g',     // 12-hour format of an hour without leading zeros 0 to 11h, not supported by php
            'H' => 'G',     // 24-hour format of an hour without leading zeros 0 to 23h
            'k' => 'G',     // 24-hour format of an hour without leading zeros 1 to 24h, not supported by php
            'hh' => 'h',    // 12-hour format of an hour with leading zeros, 01 to 12 h
            'KK' => 'h',    // 12-hour format of an hour with leading zeros, 00 to 11 h, not supported by php
            'HH' => 'H',    // 24-hour format of an hour with leading zeros, 00 to 23 h
            'kk' => 'H',    // 24-hour format of an hour with leading zeros, 01 to 24 h, not supported by php
            'm' => 'i',     // Minutes without leading zeros, not supported by php
            'mm' => 'i',    // Minutes with leading zeros
            's' => 's',     // Seconds, without leading zeros, not supported by php
            'ss' => 's',    // Seconds, with leading zeros
            'SSS' => '',    // millisecond (maximum of 3 significant digits), not supported by php
            'A' => '',      // milliseconds in day, not supported by php
            'Z' => 'O',     // Difference to Greenwich time (GMT) in hours
            'ZZ' => 'O',     // Difference to Greenwich time (GMT) in hours
            'ZZZ' => 'O',     // Difference to Greenwich time (GMT) in hours
            'z' => 'T',     // Timezone abbreviation
            'zz' => 'T',     // Timezone abbreviation
            'zzz' => 'T',     // Timezone abbreviation
            'zzzz' => 'T',  // Timzone full name, not supported by php
            'V' => 'e',      // Timezone identifier eg. Europe/Berlin
            'VV' => 'e',
            'VVV' => 'e',
            'VVVV' => 'e'
            ]);
    }
    
    /**
     * intlFormatter class (ICU based) and DateTime class don't have same format string.
     * These format patterns are completely incompatible and must be converted.
     * 
     * This method converts PHP formatted date, time or datetime string in 
     * an ICU (php intl) compatible format string.
     * 
     * @param type string $pattern: dateformat pattern like 'd.m.Y' or 'short'/'medium'/
     *          'long'/'full' or 'db
     * @param type string $type: if pattern has a name like 'short', type must define if
     * a date, time or datetime string should be formatted.
     * @return type string with converted date format pattern.
     * @throws InvalidConfigException
     */    
    public function convertPatternPhpToIcu($pattern, $type = 'date'){
        if (preg_match('/\bshort\b|\bmedium\b|\blong\b|\bfull\b/', strtolower($pattern))){
            $type = strtolower($type); 
            if ($this->_intlLoaded){
                switch ($type){
                    case 'date':
                        $formatter = new IntlDateFormatter($this->locale, $this->_dateFormatsIcu[$pattern], IntlDateFormatter::NONE, $this->timeZone);
                        break;
                    case 'time':
                        $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, $this->_dateFormatsIcu[$pattern], $this->timeZone);
                        break;
                    case 'datetime':
                        $formatter = new IntlDateFormatter($this->locale, $this->_dateFormatsIcu[$pattern], $this->_dateFormatsIcu[$pattern], $this->timeZone);
                        break;
                    default:
                        throw new InvalidConfigException('Conversion of ICU with a not supported type [date, time, datetime].');
                }
                return $formatter->getPattern();
            }    
            else {
                $localArr = FormatDefs::definition($this->locale);
                if (isset($localArr[0])){
                    return $localArr[$this->_PhpNameToPattern[$pattern][$type][1]];
                } else {
                    return $this->_PhpNameToPattern[strtolower($pattern)][$type][0];
                    // _PhpNameToPattern['short']['date'] --> 'y-m-d'
                }
            }
        } elseif ($pattern === 'db'){
            return $this->convertPatternIcuToPhp($this->dbFormat[strtolower($type)], $type);
        }
        
        return strtr($pattern, [
            'd' => 'dd',    // day with leading zeros
            'j' => 'd',     // day without leading zeros
            'D' => 'EEE',   // day written in short form eg. Sun
            'l' => 'EEEE',  // day fully written eg. Sunday
            'N' => 'e',     // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
                            // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
            'S' => '',      // engl. ordinal st, nd, rd; it's not support by ICU
            'z' => 'D',     // day of the year 0 to 365
            'W' => 'w',     // ISO-8601 week number of year, weeks starting on Monday
                            // week of the current month; isn't supported by php
                            // Day of Week in Month. eg. 2nd Wednesday in July not supported by php
                            // Modified Julian day. This is different from the conventional Julian day number in two regards. 
            'n'=> 'M',      // Numeric representation of a month, without leading zeros
            'm' => 'MM',    // Numeric representation of a month, with leading zeros
            'M' => 'MMM',   // A short textual representation of a month, three letters
            'F' => 'MMMM',  // A full textual representation of a month, such as January or March
                            // number of quarter not supported in php
                            // number of quarter '02' not supported in php
                            // quarter 'Q2' not supported in php
                            // quarter '2nd quarter' not supported in php
                            // number of quarter '2' not supported in php
            'Y' => 'yyyy',  // 4digit year eg. 2014 
            'y' => 'yy',    // 2digit year number eg. 14
                            // related Gregorian year, not supported by php
                            // ear designator like AD
            'a' => 'a',     // Lowercase Ante meridiem and Post am. or pm.
            'A' => 'a',     // Upercase Ante meridiem and Post AM or PM, not supported by ICU
            'g' => 'h',     // 12-hour format of an hour without leading zeros 1 to 12h
                            // 12-hour format of an hour without leading zeros 0 to 11h, not supported by php
            'G' => 'H',     // 24-hour format of an hour without leading zeros 0 to 23h
                            // 24-hour format of an hour without leading zeros 1 to 24h, not supported by php
            'h' => 'hh',    // 12-hour format of an hour with leading zeros, 01 to 12 h
                            // 12-hour format of an hour with leading zeros, 00 to 11 h, not supported by php
            'H' => 'HH',    // 24-hour format of an hour with leading zeros, 00 to 23 h
                            // 24-hour format of an hour with leading zeros, 01 to 24 h, not supported by php
                            // Minutes without leading zeros, not supported by php
            'i' => 'mm',    // Minutes with leading zeros
                            // Seconds, without leading zeros, not supported by php
            's' => 'ss',    // Seconds, with leading zeros
                            // millisecond (maximum of 3 significant digits), not supported by php
                            // milliseconds in day, not supported by php
            'O' => 'Z',     // Difference to Greenwich time (GMT) in hours
            'T' => 'z',     // Timezone abbreviation
                            // Timzone full name, not supported by php
            'e' => 'VV',    // Timezone identifier eg. Europe/Berlin
            'w' => '',      // Numeric representation of the day of the week 0=Sun, 6=Sat, not sup. ICU
            'T' => '',      // Number of days in the given month eg. 28 through 31, not sup. ICU
            'L' => '',      //Whether it's a leap year 1= leap, 0= normal year, not sup. ICU
            'O' => '',      // ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. not sup. ICU
            'B' => '',      // Swatch Internet time, 000 to 999, not sup. ICU
            'u' => '',      // Microseconds Note that date() will always generate 000000 since it takes an integer parameter, not sup. ICU
            'P' => '',      // Difference to Greenwich time (GMT) with colon between hours and minutes, not sup. ICU
            'Z' => '',      // Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive, not sup. ICU
            'c' => 'yyy-MM-dd\'T\'mm:HH:ssZ', //ISO 8601 date, it works only if nothing else than 'c' is in pattern.
            'r' => 'eee, dd MMM yyyy mm:HH:ss Z', // » RFC 2822 formatted date, it works only if nothing else than 'r' is in pattern
            'U' => ''       // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT), not supported in ICU
            
            ]);
    }
    /**
     * Returns the fully locale string like 'en-US' or 'de-CH'
     * @return type string
     */
    public function getLocale(){
        return $this->locale;
    }
    /**
     * Set a new local different to Yii configuration for temporale reason.
     * @param string $locale language code and country code.
     * @return \guggach\helpers\Formatter object
     */
    public function setLocale($locale = 'en-US'){
        $this->locale = $locale;
        
        // Reset dateformat pattern as requested by yii formatter config
        $this->setFormatPattern($this->_originalConfig['date'], 'date');
        $this->setFormatPattern($this->_originalConfig['time'], 'time');
        $this->setFormatPattern($this->_originalConfig['datetime'], 'datetime');
        
        $this->setDecimalSeparator();
        $this->setThousandSeparator();
       
        return $this;
    }
    /**
     * 
     * @param string $searchFor: delivers pattern for "date", "time" and "datetime"
     * @param string $patternFor: "php" or "icu" format convention. PHP is standard.
     * @return string: returns a string with format pattern requested by input parameter
     * @throws \yii\base\InvalidParamException if invalid input parameters.
     */
    public function getFormatPattern($formatFor = 'date', $patternFor = 'php') {
        $formatFor = strtolower($formatFor);
        $patternFor = strtolower($patternFor);
        
        if ($patternFor === 'php' or $patternFor === 'icu'){
            switch ($formatFor) {
                case 'date':
                    return $patternFor === 'php' ? $this->dateFormat : $this->_dateFormatIcu;
                    break;
                case 'time':
                    return $patternFor === 'php' ? $this->timeFormat : $this->_timeFormatIcu;
                    break;
                case 'datetime':
                    return $patternFor === 'php' ? $this->datetimeFormat : $this->_datetimeFormatIcu;
                    break;
                default:
                throw new \yii\base\InvalidParamException('Parameter "formatFor" is \''. $formatFor . '\'. Valid is date, time or datetime.');
            }
        } else {
                throw new \yii\base\InvalidParamException('Paramter "patternFor" is \'' .$patternFor. '\'. Valid is "php" or "icu".');
        }
    }
    
    /**
     * Sets a new date or time or datetime format and converts it from php to icu or versa.
     * @param string $format: Formatting pattern like 'd-m-Y' (php) or 'dd-mm-yyyy' icu.
     * @param string $formatFor: Specifies which target is newly formated. Option are: date, time or datetime.
     * @param string $patternFor: Specifies which pattern standard is use. PHP is standard.
     * @return Formatter object for chaining.
     * @throws \yii\base\InvalidParamException
     */
    public function setFormatPattern($format, $formatFor, $patternFor = 'php'){
        $formatFor = strtolower($formatFor);
        $patternFor = strtolower($patternFor);
        
        if (preg_match('/\bdate\b|\btime\b|\bdatetime\b/', $formatFor) != true){
            throw new \yii\base\InvalidParamException('Invalid parameter for "formatFor": "$formatFor". Allowed values are: date, time, datetime.');
        }
        
        if (preg_match('/\bshort\b|\bmedium\b|\blong\b|\bfull\b/', strtolower($format))) {
            $format = strtolower($format);
            if ($this->_intlLoaded) {
                switch ($formatFor) {
                    case 'date':
                        $this->dateFormat = $this->convertPatternIcuToPhp($format, 'date');
                        $this->_dateFormatIcu = $this->convertPatternPhpToIcu($this->dateFormat);
                        break;
                    case 'time':
                        $this->timeFormat = $this->convertPatternIcuToPhp($format, 'time');
                        $this->_timeFormatIcu = $this->convertPatternPhpToIcu($this->timeFormat);
                        break;
                    case 'datetime':
                        $this->datetimeFormat = $this->convertPatternIcuToPhp($format, 'datetime');
                        $this->_datetimeFormatIcu = $this->convertPatternPhpToIcu($this->datetimeFormat);
                        break;
                    }
            } else {
                $localArr = FormatDefs::definition($this->locale);
                if (isset($localArr[0])){
                    switch ($formatFor){
                        case 'date':
                            $this->dateFormat = $localArr[$this->_PhpNameToPattern[$format][$formatFor][1]];
                            $this->_dateFormatIcu = $this->convertPatternPhpToIcu($this->dateFormat);
                            break;
                        case 'time':
                            $this->timeFormat = $localArr[$this->_PhpNameToPattern[$format][$formatFor][1]];
                            $this->_timeFormatIcu = $this->convertPatternPhpToIcu($this->timeFormat);
                            break;
                        case 'datetime':
                            $this->datetimeFormat = $localArr[$this->_PhpNameToPattern[$format][$formatFor][1]];
                            $this->_datetimeFormatIcu = $this->convertPatternPhpToIcu($this->datetimeFormat);
                            break;
                    }
                } else {
                    // _PhpNameToPattern['short']['date'] --> 'y-m-d'
                    switch ($formatFor){
                        case 'date':
                            $this->dateFormat = $this->_PhpNameToPattern[$format][$formatFor][0];
                            $this->_dateFormatIcu = $this->convertPatternPhpToIcu($this->dateFormat);
                            break;
                        case 'time':
                            $this->timeFormat = $this->_PhpNameToPattern[$format][$formatFor][0];
                            $this->_timeFormatIcu = $this->convertPatternPhpToIcu($this->timeFormat);
                            break;
                        case 'datetime':
                            $this->datetimeFormat = $this->_PhpNameToPattern[$format][$formatFor][0];
                            $this->_datetimeFormatIcu = $this->convertPatternPhpToIcu($this->datetimeFormat);
                            break;
                    }
                }

            }
        } else {

            if ($patternFor === 'php') {
                switch ($formatFor){
                    case 'date':
                        $this->dateFormat = $format;
                        $this->_dateFormatIcu = $this->convertPatternPhpToIcu($format, $formatFor);
                        break;
                    case 'time':
                        $this->timeFormat = $format;
                        $this->_timeFormatIcu = $this->convertPatternPhpToIcu($format, $formatFor);
                        break;
                    case 'datetime':
                        $this->datetimeFormat = $format;
                        $this->_datetimeFormatIcu = $this->convertPatternPhpToIcu($format, $formatFor);
                        break;
                }
            } elseif ($patternFor === 'icu') {
                switch ($formatFor){
                    case 'date':
                        $this->dateFormat = $this->convertPatternIcuToPhp($format, $formatFor);
                        $this->_dateFormatIcu = $format;
                        break;
                    case 'time':
                        $this->timeFormat = $this->convertPatternIcuToPhp($format, $formatFor);
                        $this->_timeFormatIcu = $format;
                        break;
                    case 'datetime':
                        $this->datetimeFormat = $this->convertPatternIcuToPhp($format, $formatFor);
                        $this->_datetimeFormatIcu = $format;
                        break;
                }
            }
        }
        return $this;   
    }
    
                
/**
 * Sets the decimal separator to a defined string. If string is null the localized
 * standard (icu) will be taken. Without loaded "intl" extension the definition can be
 * adapted in FormatDefs.php.
 * @param string $sign: one sign which is set.
 * @return \guggach\helpers\Formatter
 */
    public function setDecimalSeparator($sign = null){
        if ($sign === null){
            if ($this->_intlLoaded){
                $formatter = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
                $this->decimalSeparator = $formatter->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
            } else {
                $localArr = FormatDefs::definition($this->locale);               
                if (isset($localArr[0])){
                    $this->decimalSeparator = $localArr[0];
                } else {    
                    $this->decimalSeparator = '.';
                }
            }
        } else {
            $this->decimalSeparator = $sign;
        }
    return $this;
    }
    
    public function getDecimalSeparator(){
        return $this->decimalSeparator;
    }
    
/**
 * Sets the thousand separator to a defined string. If string is null the localized
 * standard (icu) will be taken. Without loaded "intl" extension the definition can be
 * adapted in FormatDefs.php.
 * @param string $sign: one sign which is set.
 * @return \guggach\helpers\Formatter
 */
    public function setThousandSeparator($sign = null){
        if ($sign === null){
            if ($this->_intlLoaded){
                $formatter = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
                $this->thousandSeparator = $formatter->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
            } else {
                $localArr = FormatDefs::definition($this->locale);               
                if (isset($localArr[0])){
                    $this->thousandSeparator = $localArr[1];
                } else {    
                    $this->thousandSeparator = ',';
                }
            }
        } else {
            $this->thousandSeparator = $sign;
        }
    return $this;
    }

   public function getThousandSeparator(){
       return $this->thousandSeparator;
   }
    

    /**
     * Formats the value as is without any formatting.
     * This method simply returns back the parameter without any format.
     * @param mixed $value the value to be formatted
     * @return string the formatted result
     */
    public function asRaw($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return $value;
    }
    public function ufRaw($value){
        if ($value === $this->nullDisplay);
            return null;
    }
    
    /**
     * Formats the value as an HTML-encoded plain text.
     * @param mixed $value the value to be formatted
     * @return string the formatted result
     */
    public function asText($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return Html::encode($value);
    }
    public function ufText($value){
        if ($value === Html::encode($this->nullDisplay)){
            return null;
        }
    }
    
    /**
     * Formats the value as an HTML-encoded plain text with newlines converted into breaks.
     * @param mixed $value the value to be formatted
     * @return string the formatted result
     */
    public function asNtext($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return nl2br(Html::encode($value));
    }

    /**
     * Formats the value as HTML-encoded text paragraphs.
     * Each text paragraph is enclosed within a `<p>` tag.
     * One or multiple consecutive empty lines divide two paragraphs.
     * @param mixed $value the value to be formatted
     * @return string the formatted result
     */
    public function asParagraphs($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return str_replace('<p></p>', '', '<p>' . preg_replace('/[\r\n]{2,}/', "</p>\n<p>", Html::encode($value)) . '</p>');
    }

    /**
     * Formats the value as HTML text.
     * The value will be purified using [[HtmlPurifier]] to avoid XSS attacks.
     * Use [[asRaw()]] if you do not want any purification of the value.
     * @param mixed $value the value to be formatted
     * @param array|null $config the configuration for the HTMLPurifier class.
     * @return string the formatted result
     */
    public function asHtml($value, $config = null)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return HtmlPurifier::process($value, $config);
    }

    /**
     * Formats the value as a mailto link.
     * @param mixed $value the value to be formatted
     * @return string the formatted result
     */
    public function asEmail($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return Html::mailto(Html::encode($value), $value);
    }

    /**
     * Formats the value as an image tag.
     * @param mixed $value the value to be formatted
     * @return string the formatted result
     */
    public function asImage($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return Html::img($value);
    }

    /**
     * Formats the value as a hyperlink.
     * @param mixed $value the value to be formatted
     * @return string the formatted result
     */
    public function asUrl($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $url = $value;
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = 'http://' . $url;
        }

        return Html::a(Html::encode($value), $url);
    }

    /**
     * Formats the value as a boolean.
     * @param mixed $value the value to be formatted
     * @return string the formatted result
     * @see booleanFormat
     */
    public function asBoolean($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return $value ? $this->booleanFormat[1] : $this->booleanFormat[0];
    }
    public function ufBoolean($value){
        if (Yii::t('yii', 'No')){
            return false;
        } elseif (Yii::t('yii', 'Yes')){
            return true;
        } else {
            throw new InvalidParamException('Value :' . $value . ' isn\'t a boolean yes or no value.');
        }
    }
    
    /**
     * Formats the value as a date.
     * @param integer|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be parsed into a UNIX timestamp via `strtotime()`
     * - a PHP DateTime object
     *
     * @param string $targetFormat (optional): the format pattern used to convert the value into a date string.
     *                                         'short', 'medium', 'long', 'full' or pattern like 'j-n-Y' 
     * @param string $inputFormat (optional):  the format pattern of $value if it isn't a ISO or local date string.
     * @param string $formatType (optional):   Specifies the targetFormat and inputFormat pattern. Value 'php' or 'icu'
     * 
     * @return string the formatted result
     * @see dateFormat
     */
    public function asDate($value, $targetFormat = 'date', $inputFormat = null, $formatType = 'php')
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $formatType = strtolower($formatType);
        if ($formatType != 'php' and $formatType != 'icu'){
            throw new InvalidParamException('"' . $formatType . '" is not a valid value, only "php" and "icu".');
        }
        
        $value = $this->normalizeDatetimeValue($value, $inputFormat);
        if ($value === null){
            return null;
        }

        if ($this->_intlLoaded){
            if ($targetFormat === 'date') {
                $targetFormat = $this->_dateFormatIcu;
            } elseif ($targetFormat === 'db'){
              $targetFormat = $this->convertPatternPhpToIcu($this->dbFormat['date']);  
            } else {
                if ($formatType === 'php'){
                    $targetFormat = $this->convertPatternPhpToIcu($targetFormat, 'date');
                }
            }
            if (isset($this->_dateFormatsIcu[$targetFormat])) {
                $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$targetFormat], IntlDateFormatter::NONE, $this->timeZone);
            } else {
                $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $this->timeZone);
                if ($formatter !== null) {
                    $formatter->setPattern($targetFormat);
                }
            }   
            if ($formatter === null) {
                throw new InvalidConfigException(intl_get_error_message());
            }
            return $formatter->format($value);
        }else {
            if ($targetFormat === 'date') {
                $targetFormat = $this->dateFormat;
            } else {
                if ($formatType === 'php'){
                    if (isset($this->_dateFormatsIcu[$targetFormat])){  // names like "short", "medium" etc. in $format
                        $format = $this->convertPatternIcuToPhp($targetFormat, 'date');
                    } 
                } else {   // icu format
                    $format = $this->convertPatternIcuToPhp($targetFormat, 'date');
                }
            }
            $date = new DateTime('@'.$value);
            return $date->format($format);
        }
    }
    public function ufDate($value, $targetFormat = 'db', $inputFormat = null, $formatType = 'php'){
        if ($targetFormat === 'db'){
            return asDate($value, 'db', $inputFormat, $formatType);
        } elseif ($targetFormat === 'timestamp'){
            return asTimestamp($value, $inputFormat, $formatType);
        } else {
            throw new InvalidParamException('targetFormat must be "db" or "timestamp". Your value is ' . $value );
        }
    }
    
    /**
     * Formats the value as a time.
     * @param integer|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be parsed into a UNIX timestamp via `strtotime()`
     * - a PHP DateTime object
     *
     * @param string $format the format used to convert the value into a date string.
     * If null, [[timeFormat]] will be used. The format string should be one
     * that can be recognized by the PHP `date()` function.
     * @return string the formatted result
     * @see timeFormat
     */
    public function asTime($value, $targetFormat = 'time', $inputFormat = null, $formatType = 'php')
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $formatType = strtolower($formatType);
        if ($formatType != 'php' and $formatType != 'icu'){
            throw new InvalidParamException('"' . $formatType . '" is not a valid value, only "php" and "icu".');
        }
        
        $value = $this->normalizeDatetimeValue($value, $inputFormat);
        if ($value === null){
            return null;
        }

        if ($this->_intlLoaded){
            if ($targetFormat === 'time') {
                $targetFormat = $this->_timeFormatIcu;
            } elseif ($targetFormat === 'db'){
              $targetFormat = $this->convertPatternPhpToIcu($this->dbFormat['time']);  
            } else {
                if ($formatType === 'php'){
                    $targetFormat = $this->convertPatternPhpToIcu($targetFormat, 'time');
                }
            }
            if (isset($this->_dateFormatsIcu[$targetFormat])) {
                $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, $this->_dateFormats[$targetFormat], $this->timeZone);
            } else {
                $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $this->timeZone);
                if ($formatter !== null) {
                    $formatter->setPattern($targetFormat);
                }
            }   
            if ($formatter === null) {
                throw new InvalidConfigException(intl_get_error_message());
            }
            return $formatter->format($value);
        }else {
            if ($targetFormat === 'time') {
                $targetFormat = $this->timeFormat;
            } else {
                if ($formatType === 'php'){
                    if (isset($this->_dateFormatsIcu[$targetFormat])){  // names like "short", "medium" etc. in $format
                        $format = $this->convertPatternIcuToPhp($targetFormat, 'time');
                    } 
                } else {   // icu format
                    $format = $this->convertPatternIcuToPhp($targetFormat, 'time');
                }
            }
            $date = new DateTime('@'.$value);
            return $date->format($format);
        }

    }
    public function ufTime($value, $targetFormat = 'db', $inputFormat = null, $formatType = 'php'){
        if ($targetFormat === 'db'){
            return asTime($value, 'db', $inputFormat, $formatType);
        } elseif ($targetFormat === 'timestamp'){
            return asTimestamp($value, $inputFormat, $formatType);
        } else {
            throw new InvalidParamException('targetFormat must be "db" or "timestamp". Your value is ' . $value );
        }
    }

    /**
     * Formats the value as a datetime.
     * @param integer|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be parsed into a UNIX timestamp via `strtotime()`
     * - a PHP DateTime object
     *
     * @param string $format the format used to convert the value into a date string.
     * If null, [[datetimeFormat]] will be used. The format string should be one
     * that can be recognized by the PHP `date()` function.
     * @return string the formatted result
     * @see datetimeFormat
     */
    public function asDatetime($value, $targetFormat = 'datetime', $inputFormat = null, $formatType = 'php')
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $formatType = strtolower($formatType);
        if ($formatType != 'php' and $formatType != 'icu'){
            throw new InvalidParamException('"' . $formatType . '" is not a valid value, only "php" and "icu".');
        }
        
        $value = $this->normalizeDatetimeValue($value, $inputFormat);
        if ($value === null){
            return null;
        }

        if ($this->_intlLoaded){
            if ($targetFormat === 'datetime') {
                $targetFormat = $this->_datetimeFormatIcu;
            } elseif ($targetFormat === 'db'){
              $targetFormat = $this->convertPatternPhpToIcu($this->dbFormat['datetime']);  
            } else {
                if ($formatType === 'php'){
                    $targetFormat = $this->convertPatternPhpToIcu($targetFormat, 'datetime');
                }
            }
            if (isset($this->_dateFormatsIcu[$targetFormat])) {
                $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$targetFormat], $this->_dateFormats[$targetFormat], $this->timeZone);
            } else {
                $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $this->timeZone);
                if ($formatter !== null) {
                    $formatter->setPattern($targetFormat);
                }
            }   
            if ($formatter === null) {
                throw new InvalidConfigException(intl_get_error_message());
            }
            return $formatter->format($value);
        }else {
            if ($targetFormat === 'datetime') {
                $targetFormat = $this->datetimeFormat;
            } else {
                if ($formatType === 'php'){
                    if (isset($this->_dateFormatsIcu[$targetFormat])){  // names like "short", "medium" etc. in $format
                        $format = $this->convertPatternIcuToPhp($targetFormat, 'datetime');
                    } 
                } else {   // icu format
                    $format = $this->convertPatternIcuToPhp($targetFormat, 'datetime');
                }
            }
            $date = new DateTime('@'.$value);
            return $date->format($format);
        }
    }
    public function ufDatetime($value, $targetFormat = 'db', $inputFormat = null, $formatType = 'php'){
        if ($targetFormat === 'db'){
            return asDatetime($value, 'db', $inputFormat, $formatType);
        } elseif ($targetFormat === 'timestamp'){
            return asTimestamp($value, $inputFormat, $formatType);
        } else {
            throw new InvalidParamException('targetFormat must be "db" or "timestamp". Your value is ' . $value );
        }
    }

    /**
     * Formats a date, time or datetime in a float number as timestamp (seconds since 01-01-1970).
     * @param string $value Date in dbFormat or local format or individual format (see inputFormat)
     * @param string $inputFormat if the date format in value is individual the format pattern must be given here.
     * @return float with timestamp
     */
    public function asTimestamp($value, $inputFormat = null){
        return $this->normalizeDatetimeValue($value, $inputFormat);
    }
    
    /**
     * Normalizes the given datetime value as one that can be taken by various date/time formatting methods.
     *
     * @param mixed $value the datetime value to be normalized.
     * @param string $inputPattern format of $value if not database format or local format.
     * @return float the normalized datetime value (int64)
     */
    protected function normalizeDatetimeValue($value, $inputPattern = null, $patternFor = 'php')
    {
        if ($value === null){
            return null;
        }
        
        if ($inputPattern != null){
            if (strtolower($patternFor) === 'icu'){
                $FormatPatterns['individual'] = $this->convertPatternIcuToPhp($inputPattern);
            } elseif (strtolower($patternFor) === 'php') {
                $FormatPatterns['individual'] = $inputPattern;
            } else{
                throw new InvalidParamException('patternFor must be "php" or "icu" only. Your value is ' . $patternFor );
            }
        } else {
            $FormatPatterns = $this->dbFormat;
            $FormatPatterns['date'] = $this->dateFormat;
            $FormatPatterns['time'] = $this->timeFormat;
            $FormatPatterns['datetime'] = $this->datetimeFormat;
        }
        
        if (is_string($value)) {
            if (is_numeric($value) || $value === '') {
                $value = (double)$value;
            } else {
                try {
                    /** $date = new DateTime($value); ==> constructor crashes with
                     * an invalid date in $value (eg. 2014-06-35) and can't be
                     * catched by php because is fatal error. 
                     * Consequence was to find another solution which doesn't crash
                     */
                    
                    foreach($FormatPatterns as $format){
                        $date = DateTime::createFromFormat($format, $value);
                        if ( !($date === false)) break;
                    }
                    
                } catch (Exception $e) {
                    return null;
                }
                if ($date === false){
                    return null;
                }
                $value = (double)$date->format('U');
            }
            return $value;
    
            } elseif ($value instanceof DateTime || $value instanceof \DateTimeInterface) {
            return (double)$value->format('U');
        } else {
            return (double)$value; 
        } 
    }

    /**
     * Formats the value as an integer and rounds decimals with math rule
     * @param mixed $value the value to be formatted
     * @return string the formatting result.
     */
    public function asInteger($value, $grouping = true) {
        $format = null;
        
        if ($value === null) {
            return $this->nullDisplay;
        }
        if (is_string($value)) {
            $value = (float) $value;
        }    
        $value = round($value, 0);
        
        if ($this->_intlLoaded){
            $f = $this->createNumberFormatter(NumberFormatter::DECIMAL, $format);
            if ($grouping === false){
                $f->setAttribute(NumberFormatter::GROUPING_USED, false);
            }
            return $f->format($value, NumberFormatter::TYPE_INT64);
        } else {
            $grouping = $grouping === true ? $this->thousandSeparator : '';
            return number_format($value, 0, $this->decimalSeparator, $grouping);
            
        }
    }
    public function ufInteger($value){
        if ($value === null) {
            return null;
        }
        $value = $this->unformatNumber($value, 'int');
        return round($value , 0);
    }
    
    /**
     * Formats the value as a double number.
     * Property [[decimalSeparator]] will be used to represent the decimal point. The
     * value is rounded automatically to the defined decimal digits. 
     * 
     * PHP and ICU has different behaviour about number of zeros in fraction digits.
     * PHP fills up to defined decimals (eg. 2.500000 [6]) while ICU hide unnecessary digits.
     * (eg. 2.5 [6]). Until 5 fractional digits in this function is defined to 5 up with zeros.
     * 
     * @param mixed $value the value to be formatted
     * @param integer or string $decimals the number of digits after the decimal point if the value is an integer
     *          otherwise it's is a format pattern string (this works only with intl [icu]).
     * @param float $roundIncr Amount to which smaller fractation are rounded. Ex. 0.05 -> <=2.024 to 2.00 / >=2.025 to 2.05
     *          works with "intl" library only.
     * @param boolean $grouping Per standard numbers are grouped in thousands. False = no grouping
     * @return string the formatting result.
     * @see decimalSeparator
     * @see thousandSeparator
     */
    public function asDouble($value, $decimals = 2, $roundIncr = null, $grouping = true)
    {
        $format = null;
        if(is_numeric($decimals)){
            $decimals = intval($decimals);  // number of digits after decimal
        } else {
            $format = $decimals;            // format pattern for ICU only
        }
        
        if ($value === null) {
            return $this->nullDisplay;
        }
       if (is_string($value)){
            if (is_numeric($value)){
                $value = (float)$value;
            } else {
                throw new InvalidParamException('"' . $value . '" is not a numeric value.');
            }
        }
          
      //  if (true === false){
        if ($this->_intlLoaded){
            $f = $this->createNumberFormatter(NumberFormatter::DECIMAL, $format);
            if ($decimals !== null){
                $f->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
                if ($decimals <= 5){
                    $f->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
                }
            }
            if ($roundIncr == null and $this->roundingIncrement != null){
                $roundIncr = $this->roundingIncrement;
            }
            if ($roundIncr != null){
                $f->setAttribute(NumberFormatter::ROUNDING_INCREMENT, $roundIncr);
            }
            if ($grouping === false){
                $f->setAttribute(NumberFormatter::GROUPING_USED, false);
            }
            return $f->format($value);
        } else {
            
            if ($roundIncr !== null){
                $part = explode('.', (string)$roundIncr);
                if ((string)$roundIncr != '0.05'){  // exception for Swiss rounding.
                    $roundIncr = $decimals;
                    if (intval($part[0]) > 0){
                        if (substr($part[0], 0, 1) === '1'){
                           $roundIncr = (strlen($part[0]) -1) * -1 ;
                        } else {
                            throw new InvalidParamException('$roundIncr must have "1" only eg. 0.01 or 10 but not 0.02 or 20');
                        }
                    } elseif (isset($part[1]) and intval($part[1])>0) {
                        if (substr($part[1], -1) === '1'){
                            $roundIncr = strlen($part[1]);
                        } else {
                            throw new InvalidParamException('$roundIncr must have "1" only eg. 0.01 or 10 but not 0.02 or 20');
                        }
                    }
                    $value = round($value, $roundIncr);
                } else {
                    $value = round($value/5,2)*5;
                }
            }
            if ($decimals === null){
                $decimals = 0;
            }
            $grouping = $grouping === true ? $this->thousandSeparator : '';
            return number_format($value, $decimals, $this->decimalSeparator, $grouping);
            
        }
    }
    public function ufDouble($value){
        if ($value === null){
            return null;
        }
        return $this->unformatNumber($value);
    }


    /**
     * Formats the value as a number with decimal and thousand separators.
     * This method is a synomym for asDouble.
     * @param mixed $value the value to be formatted
     * @param integer $decimals the number of digits after the decimal point
     * @return string the formatted result
     * @see decimalSeparator
     * @see thousandSeparator
     */
    public function asNumber($value, $decimals = 0, $roundIncr = null, $grouping = true)
    {
        return $this->asDouble($value, $decimals, $roundIncr, $grouping);
    }
    public function ufNumber($value){
        return $this->ufDouble($value);
    }

    /**
     * Formats the value as a decimal number. This method is a synonym for asDouble
     * @see method asDouble
     * @param mixed $value the value to be formatted
     * @param string $format the format to be used. Please refer to [ICU manual](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#_details)
     * for details on how to specify a format.
     * @return string the formatted result.
     */
    public function asDecimal($value, $decimals = null, $roundIncr = null, $grouping = true)
    {
        return $this->asDouble($value, $decimals, $roundIncr, $grouping);
    }
    public function ufDecimal($value){
        return $this->ufDouble($value);
    }
    
     /**
     * Formats the value as a percent number with "%" sign.
     * @param mixed $value the value to be formatted. It must be a factor eg. 0.75 -> 75%
     * @param string $decimals Number of decimals (default = 2) or format pattern ICU
      * Please refer to [ICU manual](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#_details)
     * for details on how to specify a format.
     * @return string the formatted result.
     */
    public function asPercent($value, $decimals = 0, $grouping = true)
    {
         $format = null;
        if(is_numeric($decimals)){
            $decimals = intval($decimals);  // number of digits after decimal
        } else {
            $format = $decimals;            // format pattern for ICU only
        }
        
        if ($value === null) {
            return $this->nullDisplay;
        }
        if (is_string($value)) {
            $value = (float) $value;
        }    
         
     //   if (true === false){
        if ($this->_intlLoaded){
            $f = $this->createNumberFormatter(NumberFormatter::PERCENT, $format);
            if ($decimals !== null){
                $f->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
                if ($decimals <= 5){
                    $f->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
                }
            }
            if ($grouping === false){
                $f->setAttribute(NumberFormatter::GROUPING_USED, false);
            }
            return $f->format($value);
        } else {
            if ($decimals === null){
                $decimals = 0;
            }
            $value = $value * 100;
            $grouping = $grouping === true ? $this->thousandSeparator : '';
            return number_format($value, $decimals, $this->decimalSeparator, $grouping) . '%';
            
        }
    }
    public function ufPercent($value){
        if ($value === null){
            return null;
        }
        return $this->unformatNumber($value) / 100;
    }
    
     /**
     * Formats the value as a scientific number.
     * @param mixed $value the value to be formatted
     * @param string $format the format to be used. Please refer to [ICU manual](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#_details)
     * for details on how to specify a format.
     * @return string the formatted result.
     */
    public function asScientific($value, $decimals = null)
    {
        $format = null;
        if(is_numeric($decimals)){
            $decimals = intval($decimals);  // number of digits after decimal
        } else {
            $format = $decimals;            // format pattern for ICU only
        }
        
        if ($value === null) {
            return $this->nullDisplay;
        }
        if (is_string($value)) {
            $value = (float) $value;
        }    
         
     //   if (true === false){
        if ($this->_intlLoaded){
            $f = $this->createNumberFormatter(NumberFormatter::SCIENTIFIC, $format);
            if ($decimals !== null){
                $f->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            }
            return $f->format($value);
        } else {
            if ($decimals !== null){
                return sprintf("%.{$decimals}E", $value);
            } else {
                return sprintf("%.E", $value);
            }
        }

    }
    public function ufScientific($value){
        if ($value === null){
            return null;
        }
        $value = $value + 0;
        if (is_float($value)){
            return $value;
        } else {
            throw new InvalidParamException('Parameter value must be a scientific value, not ' . $value);
        }
    }
    
     /**
     * Formats the value as a currency number.
     * @param mixed $value the value to be formatted
     * @param string $currency the 3-letter ISO 4217 currency code indicating the currency to use.
     * @param float $roundIncr: Amount to which smaller fractation are rounded. Ex. 0.05 -> <=2.024 to 2.00 / >=2.025 to 2.05
     *         works with "intl" library only.
     * @param string $format the format to be used. Please refer to [ICU manual](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#_details)
     * for details on how to specify a format.
     * @return string the formatted result.
     */
    public function asCurrency($value, $currency = null, $roundIncr = null,  $grouping = true)
    {
        $format = null;
        
        if ($value === null) {
            return $this->nullDisplay;
        }
        if (is_string($value)){
            if (is_numeric($value)){
                $value = (float)$value;
            } else {
                throw new InvalidParamException('"' . $value . '" is not a numeric value.');
            }
        }
        
        if ($currency === null and $this->currencyCode != null){
            $currency = $this->currencyCode;
        }
        if ($roundIncr === null and $this->roundingIncrCurrency != null){
            $roundIncr = $this->roundingIncrCurrency;
        }        
        
       // if (true == false){
        if ($this->_intlLoaded) {
            if (trim($currency) === '' and $currency !== null){
                $f = $this->createNumberFormatter(NumberFormatter::DECIMAL, $format);
            } else {
                $f = $this->createNumberFormatter(NumberFormatter::CURRENCY, $format);                
            }

            if ($grouping === false){
                $f->setAttribute(NumberFormatter::GROUPING_USED, false);
            }
            if ($roundIncr !== null){
                $f->setAttribute(NumberFormatter::ROUNDING_INCREMENT, $roundIncr);
            }
            if ($currency === null){
                return $f->format($value);
            } else {
                return $f->formatCurrency($value, $currency);
            }
        } else {
            $localArr = FormatDefs::definition($this->locale);
            if ($currency === null){
                if (isset($localArr[14])){
                    $currency = $localArr[14];  // 14 = currency code
                } else {
                    $currency = 'USD';
                }
            }
            
            $value = $currency . ' ' . $this->asDouble($value, 2, $roundIncr, $grouping);
            
            $t = 'test';
            return $value;
        }
    }
    public function ufCurrency($value){
        if ($value === null){
            return null;
        }
        return $this->unformatNumber($value);
    }
    
    /**
     * Formats the value in bytes as a size in human readable form.
     * @param integer $value value in bytes to be formatted
     * @param boolean $verbose if full names should be used (e.g. bytes, kilobytes, ...).
     * Defaults to false meaning that short names will be used (e.g. B, KB, ...).
     * @return string the formatted result
     * @see sizeFormat
     */
    public function asSize($value, $verbose = false)
    {
        $position = 0;

        do {
            if ($value < $this->sizeFormat['base']) {
                break;
            }

            $value = $value / $this->sizeFormat['base'];
            $position++;
        } while ($position < 6);

        $value = round($value, $this->sizeFormat['decimals']);
        $formattedValue = isset($this->sizeFormat['decimalSeparator']) ? str_replace('.', $this->sizeFormat['decimalSeparator'], $value) : $value;
        $params = ['n' => $formattedValue];

        switch ($position) {
            case 0:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# byte} other{# bytes}}', $params) : Yii::t('yii', '{n} B', $params);
            case 1:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# kilobyte} other{# kilobytes}}', $params) : Yii::t('yii', '{n} KB', $params);
            case 2:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# megabyte} other{# megabytes}}', $params) : Yii::t('yii', '{n} MB', $params);
            case 3:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# gigabyte} other{# gigabytes}}', $params) : Yii::t('yii', '{n} GB', $params);
            case 4:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# terabyte} other{# terabytes}}', $params) : Yii::t('yii', '{n} TB', $params);
            default:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# petabyte} other{# petabytes}}', $params) : Yii::t('yii', '{n} PB', $params);
        }
    }
    public function ufSize($value){
        $messures = ['b', 'kb', 'mb', 'gb' , 'tb', 'pb' ,'bytes', 'kilobytes' , 'megabytes', 'gigabytes', 'terabytes', 'petabytes',
                      'byte', 'kilobyte' , 'megabyte', 'gigabyte', 'terabyte', 'petabyte',
                      'o', 'ko', 'mo', 'go', 'to', 'po', 'octet', 'kilooctet', 'megaoctet', 'gigaoctet' , 'teraoctet', 'petaoctet',
                     'octets', 'kilooctets', 'megaoctets', 'gigaoctets' , 'teraoctets', 'petaoctets'];
        
        if ($value === null){
            return null;
        }
        
        $found = false;
        $ufValue = $this->unformatNumber($value);
                
        foreach ($messures as $key => $search) {
            if (preg_match('/\b'.$search.'\b/i', $value)) {
                $found = true;
                break;
           }
        }
        if ($found === true){
            $pos = $key % 6;
            while ($pos > 0) {  // kb or more
                $ufValue = $ufValue * $this->sizeFormat['base'];
                $pos--;
            }
        } else {
            throw new InvalidParamException('Parameter value isn\'t memory size formatted string like Mb.' );
        }
        return $ufValue;
    }
    
    /**
     * Formats the value as the time interval between a date and now in human readable form.
     *
     * @param integer|string|DateTime|\DateInterval $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be parsed into a UNIX timestamp via `strtotime()` or that can be passed to a DateInterval constructor.
     * - a PHP DateTime object
     * - a PHP DateInterval object (a positive time interval will refer to the past, a negative one to the future)
     *
     * @param integer|string|DateTime|\DateInterval $referenceTime if specified the value is used instead of now
     * @return string the formatted result
     */
    public function asRelativeTime($value, $referenceTime = null)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        if ($value instanceof \DateInterval) {
            $interval = $value;
        } else {
            $timestamp = $this->normalizeDatetimeValue($value);

            if ($timestamp === false) {
                // $value is not a valid date/time value, so we try
                // to create a DateInterval with it
                try {
                    $interval = new \DateInterval($value);
                } catch (\Exception $e) {
                    // invalid date/time and invalid interval
                    return $this->nullDisplay;
                }
            } else {
                $timezone = new \DateTimeZone($this->timeZone);

                if ($referenceTime === null) {
                    $dateNow = new DateTime('now', $timezone);
                } else {
                    $referenceTime = $this->normalizeDatetimeValue($referenceTime);
                    $dateNow = new DateTime(null, $timezone);
                    $dateNow->setTimestamp($referenceTime);
                }

                $dateThen = new DateTime(null, $timezone);
                $dateThen->setTimestamp($timestamp);

                $interval = $dateThen->diff($dateNow);
            }
        }

        if ($interval->invert) {
            if ($interval->y >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a year} other{# years}}', ['delta' => $interval->y]);
            }
            if ($interval->m >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a month} other{# months}}', ['delta' => $interval->m]);
            }
            if ($interval->d >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a day} other{# days}}', ['delta' => $interval->d]);
            }
            if ($interval->h >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{an hour} other{# hours}}', ['delta' => $interval->h]);
            }
            if ($interval->i >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a minute} other{# minutes}}', ['delta' => $interval->i]);
            }

            return Yii::t('yii', 'in {delta, plural, =1{a second} other{# seconds}}', ['delta' => $interval->s]);
        } else {
            if ($interval->y >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a year} other{# years}} ago', ['delta' => $interval->y]);
            }
            if ($interval->m >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a month} other{# months}} ago', ['delta' => $interval->m]);
            }
            if ($interval->d >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a day} other{# days}} ago', ['delta' => $interval->d]);
            }
            if ($interval->h >= 1) {
                return Yii::t('yii', '{delta, plural, =1{an hour} other{# hours}} ago', ['delta' => $interval->h]);
            }
            if ($interval->i >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a minute} other{# minutes}} ago', ['delta' => $interval->i]);
            }

            return Yii::t('yii', '{delta, plural, =1{a second} other{# seconds}} ago', ['delta' => $interval->s]);
        }
    }
    /**
     * Creates a number formatter based on the given type and format.
     * @param integer $type the type of the number formatter
     * Values: NumberFormatter::DECIMAL, ::CURRENCY, ::PERCENT, ::SCIENTIFIC, ::SPELLOUT, ::ORDINAL
     *          ::DURATION, ::PATTERN_RULEBASED, ::DEFAULT_STYLE, ::IGNORE
     * @param string $format the format to be used. Please refer to 
     * [ICU manual](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#_details)
     * @return NumberFormatter the created formatter instance
     */
    protected function createNumberFormatter($type, $format)
    {
        $formatter = new NumberFormatter($this->locale, $type);
        if ($format !== null) {
            $formatter->setPattern($format);
        } else {
            $formatter->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $this->decimalSeparator);
            $formatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $this->thousandSeparator);
        }
        
        if (!empty($this->numberFormatOptions)) {
            foreach ($this->numberFormatOptions as $name => $attribute) {
                $formatter->setAttribute($name, $attribute);
            }
        }
        if (!empty($this->numberTextFormatOptions)) {
            foreach ($this->numberTextFormatOptions as $name => $attribute) {
                $formatter->setTextAttribute($name, $attribute);
            }
        }

        return $formatter;
    }
    
    /**
     * Removes formatting information for a "numeric" string and sets a "."
     * as decimalseparator.
     * @param string $value formatted number/currency like "EUR 13.250,53"
     * @return float of unformatted machine readable number like "13250.53"
     */
    protected function unformatNumber($value, $numberType = 'dec'){
        
        if ($value === null) {
            return null;
        }
        
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $value);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $value);

        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        if ($numberType != 'dec'){  // integer only
            $stringWithCommaOrDot = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);
        }

        return (float) str_replace(',', '.', $stringWithCommaOrDot);
    }
        
}
