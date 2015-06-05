<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\web\JsExpression;

/**
 * BaseJsHelper provides concrete implementation for [[JsHelper]].
 *
 * Do not use BaseJsHelper. Use [[JsHelper]] instead.
 *
 * @author Dima Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.5
 */
class BaseJsHelper
{
    /**
     * Escapes regular expression to use in JavaScript (client-side)
     * @param string $regexp
     * @return JsExpression
     */
    public static function escapeRegexp ($regexp) {
        $pattern = preg_replace('/\\\\x\{?([0-9a-fA-F]+)\}?/', '\u$1', $regexp);
        $deliminator = substr($pattern, 0, 1);
        $pos = strrpos($pattern, $deliminator, 1);
        $flag = substr($pattern, $pos + 1);
        if ($deliminator !== '/') {
            $pattern = '/' . str_replace('/', '\\/', substr($pattern, 1, $pos - 1)) . '/';
        } else {
            $pattern = substr($pattern, 0, $pos + 1);
        }
        if (!empty($flag)) {
            $pattern .= preg_replace('/[^igm]/', '', $flag);
        }

        return new JsExpression($pattern);
    }
}
