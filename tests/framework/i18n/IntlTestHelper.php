<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

// override information about intl

namespace yiiunit\framework\i18n {
    use yiiunit\TestCase;

    class IntlTestHelper
    {
        public static $enableIntl;

        /**
         * emulate disabled intl extension
         *
         * enable it only for tests prefixed with testIntl
         * @param Testcase $test
         */
        public static function setIntlStatus($test)
        {
            static::$enableIntl = null;
            if (strncmp($test->getName(false), 'testIntl', 8) === 0) {
                if (!extension_loaded('intl')) {
                    $test->markTestSkipped('intl extension is not installed.');
                }
                static::$enableIntl = true;
            } else {
                static::$enableIntl = false;
            }
        }

        public static function resetIntlStatus()
        {
            static::$enableIntl = null;
        }
    }
}

namespace yii\i18n {
    use yiiunit\framework\i18n\IntlTestHelper;

    if (!function_exists('yii\i18n\extension_loaded')) {
        function extension_loaded($name)
        {
            if ($name === 'intl' && IntlTestHelper::$enableIntl !== null) {
                return IntlTestHelper::$enableIntl;
            }
            return \extension_loaded($name);
        }
    }
}

namespace yii\helpers {
    use yiiunit\framework\i18n\IntlTestHelper;

    if (!function_exists('yii\helpers\extension_loaded')) {
        function extension_loaded($name)
        {
            if ($name === 'intl' && IntlTestHelper::$enableIntl !== null) {
                return IntlTestHelper::$enableIntl;
            }
            return \extension_loaded($name);
        }
    }
}

namespace yii\validators {
    use yiiunit\framework\i18n\IntlTestHelper;

    if (!function_exists('yii\validators\extension_loaded')) {
        function extension_loaded($name)
        {
            if ($name === 'intl' && IntlTestHelper::$enableIntl !== null) {
                return IntlTestHelper::$enableIntl;
            }
            return \extension_loaded($name);
        }
    }
}
