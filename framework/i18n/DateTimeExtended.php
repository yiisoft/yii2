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
 * **Important Note:** This implementation was created to be used by [[Formatter]] internally, it may not behave
 * as expected when used directly in your code. Normally you should not need to use this class in your application code.
 * Use the original PHP [DateTime](http://php.net/manual/en/class.datetime.php) class instead.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0.1
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
        parent::__construct($time, $timezone);

        $info = date_parse($time);
        if ($info['hour'] === false && $info['minute'] === false && $info['second'] === false) {
            $this->_isDateOnly = true;
        } else {
            $this->_isDateOnly = false;
        }
    }

    /**
     * Parse a string into a new DateTime object according to the specified format
     * @param string $format Format accepted by date().
     * @param string $time String representing the time.
     * @param \DateTimeZone $timezone A DateTimeZone object representing the desired time zone.
     * @return DateTimeExtended
     * @link http://php.net/manual/en/datetime.createfromformat.php
     */
    public static function createFromFormat ($format, $time, $timezone = null)
    {
        if (($originalDateTime = parent::createFromFormat($format, $time, $timezone)) === false) {
            return false;
        }
        $info = date_parse_from_format($format, $time);

        /** @var $dateTime \DateTime */
        $dateTime = new static;
        if ($info['hour'] === false && $info['minute'] === false && $info['second'] === false) {
            $dateTime->_isDateOnly = true;
        } else {
            $dateTime->_isDateOnly = false;
        }
        $dateTime->setTimezone($originalDateTime->getTimezone());
        $dateTime->setTimestamp($originalDateTime->getTimestamp());

        return $dateTime;
    }

    public function isDateOnly()
    {
        return $this->_isDateOnly;
    }

    public function getTimezone()
    {
        if ($this->_isDateOnly) {
            return false;
        } else {
            return parent::getTimezone();
        }
    }

    public function getOffset()
    {
        if ($this->_isDateOnly) {
            return false;
        } else {
            return parent::getOffset();
        }
    }
}
