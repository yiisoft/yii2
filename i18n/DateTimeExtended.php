<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

/**
 * DateTimeExtended is an extended version of the [PHP DateTime class](http://php.net/manual/en/class.datetime.php).
 *
 * It provides more accurate handling of date-only values which can not be converted between timezone as
 * they do not include any time information.
 *
 * **Important Note:** This implementation was created to be used internally of [[Formatter]], it may not behave
 * as expected when used directly in your code. You should normally not need to use this class in your application code.
 * Use the original PHP [DateTime](http://php.net/manual/en/class.datetime.php) class instead.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class DateTimeExtended extends \DateTime
{
    private $_isDateOnly = false;

    /**
     * The DateTimeExtended constructor.
     *
     * @param string $time
     * @param \DateTimeZone $timezone
     * @return DateTimeExtended
     * @see http://php.net/manual/en/datetime.construct.php
     */
    public function __construct ($time = 'now', \DateTimeZone $timezone = null)
    {
        // TODO get date info
        $this->_isDateOnly = false;
        parent::__construct($time, $timezone);
    }

    /**
     * Parse a string into a new DateTime object according to the specified format
     * @param string $format Format accepted by date().
     * @param string $time String representing the time.
     * @param \DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
     * @return DateTimeExtended
     * @link http://php.net/manual/en/datetime.createfromformat.php
     */
    public static function createFromFormat ($format, $time, \DateTimeZone $timezone=null)
    {
        $dateTime = parent::createFromFormat($format, $time, $timezone);
        // TODO turn object into instance of $this
        // TODO get date info
//        $dateTime->_isDateOnly = false;
        return $dateTime;
    }

    public function isDateOnly()
    {
        return $this->_isDateOnly;
    }
}