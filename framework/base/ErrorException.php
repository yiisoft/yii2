<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ErrorException represents a PHP error.
 *
 * For more details and usage information on ErrorException, see the [guide article on handling errors](guide:runtime-handling-errors).
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class ErrorException extends \ErrorException
{
    /**
     * This constant represents a fatal error in the HHVM engine.
     *
     * PHP Zend runtime won't call the error handler on fatals, HHVM will, with an error code of 16777217
     * We will handle fatal error a bit different on HHVM.
     * @see https://github.com/facebook/hhvm/blob/master/hphp/runtime/base/runtime-error.h#L62
     * @since 2.0.6
     */
    const E_HHVM_FATAL_ERROR = 16777217; // E_ERROR | (1 << 24)


    /**
     * Constructs the exception.
     * @link https://www.php.net/manual/en/errorexception.construct.php
     * @param string $message [optional]
     * @param int $code [optional]
     * @param int $severity [optional]
     * @param string $filename [optional]
     * @param int $lineno [optional]
     * @param \Throwable|null $previous [optional]
     */
    public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);

        if ($this->isXdebugStackAvailable()) {
            // Xdebug trace can't be modified and used directly with PHP 7
            // @see https://github.com/yiisoft/yii2/pull/11723
            $xdebugTrace = array_slice(array_reverse(xdebug_get_function_stack()), 1, -1);
            $trace = [];
            foreach ($xdebugTrace as $frame) {
                if (!isset($frame['function'])) {
                    $frame['function'] = 'unknown';
                }

                // Xdebug < 2.1.1: https://bugs.xdebug.org/view.php?id=695
                if (!isset($frame['type']) || $frame['type'] === 'static') {
                    $frame['type'] = '::';
                } elseif ($frame['type'] === 'dynamic') {
                    $frame['type'] = '->';
                }

                // Xdebug has a different key name
                if (isset($frame['params']) && !isset($frame['args'])) {
                    $frame['args'] = $frame['params'];
                }
                $trace[] = $frame;
            }

            $ref = new \ReflectionProperty('Exception', 'trace');
            $ref->setAccessible(true);
            $ref->setValue($this, $trace);
        }
    }

    /**
     * Ensures that Xdebug stack trace is available based on Xdebug version.
     * Idea taken from developer bishopb at https://github.com/rollbar/rollbar-php
     * @return bool
     */
    private function isXdebugStackAvailable()
    {
        if (!function_exists('xdebug_get_function_stack')) {
            return false;
        }

        // check for Xdebug being installed to ensure origin of xdebug_get_function_stack()
        $version = phpversion('xdebug');
        if ($version === false) {
            return false;
        }

        // Xdebug 2 and prior
        if (version_compare($version, '3.0.0', '<')) {
            return true;
        }

        // Xdebug 3 and later, proper mode is required
        return false !== strpos(ini_get('xdebug.mode'), 'develop');
    }

    /**
     * Returns if error is one of fatal type.
     *
     * @param array $error error got from error_get_last()
     * @return bool if error is one of fatal type
     */
    public static function isFatalError($error)
    {
        return isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, self::E_HHVM_FATAL_ERROR]);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        static $names = [
            E_COMPILE_ERROR => 'PHP Compile Error',
            E_COMPILE_WARNING => 'PHP Compile Warning',
            E_CORE_ERROR => 'PHP Core Error',
            E_CORE_WARNING => 'PHP Core Warning',
            E_DEPRECATED => 'PHP Deprecated Warning',
            E_ERROR => 'PHP Fatal Error',
            E_NOTICE => 'PHP Notice',
            E_PARSE => 'PHP Parse Error',
            E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
            E_STRICT => 'PHP Strict Warning',
            E_USER_DEPRECATED => 'PHP User Deprecated Warning',
            E_USER_ERROR => 'PHP User Error',
            E_USER_NOTICE => 'PHP User Notice',
            E_USER_WARNING => 'PHP User Warning',
            E_WARNING => 'PHP Warning',
            self::E_HHVM_FATAL_ERROR => 'HHVM Fatal Error',
        ];

        return isset($names[$this->getCode()]) ? $names[$this->getCode()] : 'Error';
    }
}
