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
         * Emulate disabled intl extension.
         *
         * Enable it only for tests prefixed with testIntl.
         * @param Testcase $test
         */
        public static function setIntlStatus($test)
        {
            static::$enableIntl = null;
            if (strncmp($test->getName(false), 'testIntl', 8) === 0) {
                static::$enableIntl = true;

                if (version_compare(PHP_VERSION, '7.2.0.RC.1', '>=') && version_compare(PHP_VERSION, '7.2.0.RC.3', '<=')) {
                    // IntlDateFormatter::parse() is broken in PHP 7.2. Disabled INTL tests until regression is fixed:
                    // https://bugs.php.net/bug.php?id=75378
                    $test->markTestSkipped('intl extension is broken in PHP 7.2');
                    return;
                }

                if (!extension_loaded('intl')) {
                    $test->markTestSkipped('intl extension is not installed.');
                }
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
