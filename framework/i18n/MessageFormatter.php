<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\Component;
use yii\base\NotSupportedException;

/**
 * MessageFormatter allows formatting messages via [ICU message format](http://userguide.icu-project.org/formatparse/messages)
 *
 * This class enhances the message formatter class provided by the PHP intl extension.
 *
 * The following enhancements are provided:
 *
 * - It accepts named arguments and mixed numeric and named arguments.
 * - Issues no error when an insufficient number of arguments have been provided. Instead, the placeholders will not be
 *   substituted.
 * - Fixes PHP 5.5 weird placeholder replacement in case no arguments are provided at all (https://bugs.php.net/bug.php?id=65920).
 * - Offers limited support for message formatting in case PHP intl extension is not installed.
 *   However it is highly recommended that you install [PHP intl extension](http://php.net/manual/en/book.intl.php) if you want
 *   to use MessageFormatter features.
 *
 *   The fallback implementation only supports the following message formats:
 *   - plural formatting for english ('one' and 'other' selectors)
 *   - select format
 *   - simple parameters
 *   - integer number parameters
 *
 *   The fallback implementation does NOT support the ['apostrophe-friendly' syntax](http://www.php.net/manual/en/messageformatter.formatmessage.php).
 *   Also messages that are working with the fallback implementation are not necessarily compatible with the
 *   PHP intl MessageFormatter so do not rely on the fallback if you are able to install intl extension somehow.
 *
 * @property string $errorCode Code of the last error. This property is read-only.
 * @property string $errorMessage Description of the last error. This property is read-only.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class MessageFormatter extends Component
{
    private $_errorCode = 0;
    private $_errorMessage = '';

    /**
     * Get the error code from the last operation
     * @link http://php.net/manual/en/messageformatter.geterrorcode.php
     * @return string Code of the last error.
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * Get the error text from the last operation
     * @link http://php.net/manual/en/messageformatter.geterrormessage.php
     * @return string Description of the last error.
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * Formats a message via [ICU message format](http://userguide.icu-project.org/formatparse/messages)
     *
     * It uses the PHP intl extension's [MessageFormatter](http://www.php.net/manual/en/class.messageformatter.php)
     * and works around some issues.
     * If PHP intl is not installed a fallback will be used that supports a subset of the ICU message format.
     *
     * @param string $pattern The pattern string to insert parameters into.
     * @param array $params The array of name value pairs to insert into the format string.
     * @param string $language The locale to use for formatting locale-dependent parts
     * @return string|boolean The formatted pattern string or `FALSE` if an error occurred
     */
    public function format($pattern, $params, $language)
    {
        $this->_errorCode = 0;
        $this->_errorMessage = '';

        if ($params === []) {
            return $pattern;
        }

        if (!class_exists('MessageFormatter', false)) {
            return $this->fallbackFormat($pattern, $params, $language);
        }

        if (version_compare(PHP_VERSION, '5.5.0', '<') || version_compare(INTL_ICU_VERSION, '4.8', '<')) {
            // replace named arguments
            $pattern = $this->replaceNamedArguments($pattern, $params, $newParams);
            $params = $newParams;
        }

        $formatter = new \MessageFormatter($language, $pattern);
        if ($formatter === null) {
            $this->_errorCode = intl_get_error_code();
            $this->_errorMessage = "Message pattern is invalid: " . intl_get_error_message();

            return false;
        }
        $result = $formatter->format($params);
        if ($result === false) {
            $this->_errorCode = $formatter->getErrorCode();
            $this->_errorMessage = $formatter->getErrorMessage();

            return false;
        } else {
            return $result;
        }
    }

    /**
     * Parses an input string according to an [ICU message format](http://userguide.icu-project.org/formatparse/messages) pattern.
     *
     * It uses the PHP intl extension's [MessageFormatter::parse()](http://www.php.net/manual/en/messageformatter.parsemessage.php)
     * and adds support for named arguments.
     * Usage of this method requires PHP intl extension to be installed.
     *
     * @param string $pattern The pattern to use for parsing the message.
     * @param string $message The message to parse, conforming to the pattern.
     * @param string $language The locale to use for formatting locale-dependent parts
     * @return array|boolean An array containing items extracted, or `FALSE` on error.
     * @throws \yii\base\NotSupportedException when PHP intl extension is not installed.
     */
    public function parse($pattern, $message, $language)
    {
        $this->_errorCode = 0;
        $this->_errorMessage = '';

        if (!class_exists('MessageFormatter', false)) {
            throw new NotSupportedException('You have to install PHP intl extension to use this feature.');
        }

        // replace named arguments
        if (($tokens = self::tokenizePattern($pattern)) === false) {
            $this->_errorCode = -1;
            $this->_errorMessage = "Message pattern is invalid.";

            return false;
        }
        $map = [];
        foreach ($tokens as $i => $token) {
            if (is_array($token)) {
                $param = trim($token[0]);
                if (!isset($map[$param])) {
                    $map[$param] = count($map);
                }
                $token[0] = $map[$param];
                $tokens[$i] = '{' . implode(',', $token) . '}';
            }
        }
        $pattern = implode('', $tokens);
        $map = array_flip($map);

        $formatter = new \MessageFormatter($language, $pattern);
        if ($formatter === null) {
            $this->_errorCode = -1;
            $this->_errorMessage = "Message pattern is invalid.";

            return false;
        }
        $result = $formatter->parse($message);
        if ($result === false) {
            $this->_errorCode = $formatter->getErrorCode();
            $this->_errorMessage = $formatter->getErrorMessage();

            return false;
        } else {
            $values = [];
            foreach ($result as $key => $value) {
                $values[$map[$key]] = $value;
            }

            return $values;
        }
    }

    /**
     * Replace named placeholders with numeric placeholders and quote unused.
     *
     * @param string $pattern The pattern string to replace things into.
     * @param array $givenParams The array of values to insert into the format string.
     * @param array $resultingParams Modified array of parameters.
     * @param array $map
     * @return string The pattern string with placeholders replaced.
     */
    private function replaceNamedArguments($pattern, $givenParams, &$resultingParams, &$map = [])
    {
        if (($tokens = self::tokenizePattern($pattern)) === false) {
            return false;
        }
        foreach ($tokens as $i => $token) {
            if (!is_array($token)) {
                continue;
            }
            $param = trim($token[0]);
            if (isset($givenParams[$param])) {
                // if param is given, replace it with a number
                if (!isset($map[$param])) {
                    $map[$param] = count($map);
                    // make sure only used params are passed to format method
                    $resultingParams[$map[$param]] = $givenParams[$param];
                }
                $token[0] = $map[$param];
                $quote = "";
            } else {
                // quote unused token
                $quote = "'";
            }
            $type = isset($token[1]) ? trim($token[1]) : 'none';
            // replace plural and select format recursively
            if ($type == 'plural' || $type == 'select') {
                if (!isset($token[2])) {
                    return false;
                }
                if (($subtokens = self::tokenizePattern($token[2])) === false) {
                    return false;
                }
                $c = count($subtokens);
                for ($k = 0; $k + 1 < $c; $k++) {
                    if (is_array($subtokens[$k]) || !is_array($subtokens[++$k])) {
                        return false;
                    }
                    $subpattern = $this->replaceNamedArguments(implode(',', $subtokens[$k]), $givenParams, $resultingParams, $map);
                    $subtokens[$k] = $quote . '{' . $quote . $subpattern . $quote . '}' . $quote;
                }
                $token[2] = implode('', $subtokens);
            }
            $tokens[$i] = $quote . '{' . $quote . implode(',', $token) . $quote . '}' . $quote;
        }

        return implode('', $tokens);
    }

    /**
     * Fallback implementation for MessageFormatter::formatMessage
     * @param string $pattern The pattern string to insert things into.
     * @param array $args The array of values to insert into the format string
     * @param string $locale The locale to use for formatting locale-dependent parts
     * @return string|boolean The formatted pattern string or `FALSE` if an error occurred
     */
    protected function fallbackFormat($pattern, $args, $locale)
    {
        if (($tokens = self::tokenizePattern($pattern)) === false) {
            $this->_errorCode = -1;
            $this->_errorMessage = "Message pattern is invalid.";

            return false;
        }
        foreach ($tokens as $i => $token) {
            if (is_array($token)) {
                if (($tokens[$i] = $this->parseToken($token, $args, $locale)) === false) {
                    $this->_errorCode = -1;
                    $this->_errorMessage = "Message pattern is invalid.";

                    return false;
                }
            }
        }

        return implode('', $tokens);
    }

    /**
     * Tokenizes a pattern by separating normal text from replaceable patterns
     * @param string $pattern patter to tokenize
     * @return array|boolean array of tokens or false on failure
     */
    private static function tokenizePattern($pattern)
    {
        $depth = 1;
        if (($start = $pos = mb_strpos($pattern, '{')) === false) {
            return [$pattern];
        }
        $tokens = [mb_substr($pattern, 0, $pos)];
        while (true) {
            $open = mb_strpos($pattern, '{', $pos + 1);
            $close = mb_strpos($pattern, '}', $pos + 1);
            if ($open === false && $close === false) {
                break;
            }
            if ($open === false) {
                $open = mb_strlen($pattern);
            }
            if ($close > $open) {
                $depth++;
                $pos = $open;
            } else {
                $depth--;
                $pos = $close;
            }
            if ($depth == 0) {
                $tokens[] = explode(',', mb_substr($pattern, $start + 1, $pos - $start - 1), 3);
                $start = $pos + 1;
                $tokens[] = mb_substr($pattern, $start, $open - $start);
                $start = $open;
            }
        }
        if ($depth != 0) {
            return false;
        }

        return $tokens;
    }

    /**
     * Parses a token
     * @param array $token the token to parse
     * @param array $args arguments to replace
     * @param string $locale the locale
     * @return bool|string parsed token or false on failure
     * @throws \yii\base\NotSupportedException when unsupported formatting is used.
     */
    private function parseToken($token, $args, $locale)
    {
        // parsing pattern based on ICU grammar:
        // http://icu-project.org/apiref/icu4c/classMessageFormat.html#details

        $param = trim($token[0]);
        if (isset($args[$param])) {
            $arg = $args[$param];
        } else {
            return '{' . implode(',', $token) . '}';
        }
        $type = isset($token[1]) ? trim($token[1]) : 'none';
        switch ($type) {
            case 'date':
            case 'time':
            case 'spellout':
            case 'ordinal':
            case 'duration':
            case 'choice':
            case 'selectordinal':
                throw new NotSupportedException("Message format '$type' is not supported. You have to install PHP intl extension to use this feature.");
            case 'number':
                if (is_int($arg) && (!isset($token[2]) || trim($token[2]) == 'integer')) {
                    return $arg;
                }
                throw new NotSupportedException("Message format 'number' is only supported for integer values. You have to install PHP intl extension to use this feature.");
            case 'none':
                return $arg;
            case 'select':
                /* http://icu-project.org/apiref/icu4c/classicu_1_1SelectFormat.html
                selectStyle = (selector '{' message '}')+
                */
                if (!isset($token[2])) {
                    return false;
                }
                $select = self::tokenizePattern($token[2]);
                $c = count($select);
                $message = false;
                for ($i = 0; $i + 1 < $c; $i++) {
                    if (is_array($select[$i]) || !is_array($select[$i + 1])) {
                        return false;
                    }
                    $selector = trim($select[$i++]);
                    if ($message === false && $selector == 'other' || $selector == $arg) {
                        $message = implode(',', $select[$i]);
                    }
                }
                if ($message !== false) {
                    return $this->fallbackFormat($message, $args, $locale);
                }
            break;
            case 'plural':
                /* http://icu-project.org/apiref/icu4c/classicu_1_1PluralFormat.html
                pluralStyle = [offsetValue] (selector '{' message '}')+
                offsetValue = "offset:" number
                selector = explicitValue | keyword
                explicitValue = '=' number  // adjacent, no white space in between
                keyword = [^[[:Pattern_Syntax:][:Pattern_White_Space:]]]+
                message: see MessageFormat
                */
                if (!isset($token[2])) {
                    return false;
                }
                $plural = self::tokenizePattern($token[2]);
                $c = count($plural);
                $message = false;
                $offset = 0;
                for ($i = 0; $i + 1 < $c; $i++) {
                    if (is_array($plural[$i]) || !is_array($plural[$i + 1])) {
                        return false;
                    }
                    $selector = trim($plural[$i++]);
                    if ($i == 1 && substr($selector, 0, 7) == 'offset:') {
                        $offset = (int) trim(mb_substr($selector, 7, ($pos = mb_strpos(str_replace(["\n", "\r", "\t"], ' ', $selector), ' ', 7)) - 7));
                        $selector = trim(mb_substr($selector, $pos + 1));
                    }
                    if ($message === false && $selector == 'other' ||
                        $selector[0] == '=' && (int) mb_substr($selector, 1) == $arg ||
                        $selector == 'one' && $arg - $offset == 1
                    ) {
                        $message = implode(',', str_replace('#', $arg - $offset, $plural[$i]));
                    }
                }
                if ($message !== false) {
                    return $this->fallbackFormat($message, $args, $locale);
                }
                break;
        }

        return false;
    }
}
