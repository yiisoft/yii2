<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

/**
 * Url provides a set of static methods for managing URLs.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class Url extends BaseUrl
{
    /**
     * @param        $params  Array of values to convert into http query
     * @param string $keyfather Inheritance of keys
     * @param string $response
     * @param bool   $ltrim
     *
     * @return string
     */
    public static function custom_http_build_query($params, $keyfather = '', $response = '', $ltrim = true) {
        foreach($params as $key => $value) {
            if(!empty($keyfather)) {
                if(is_numeric($key)) {
                    $key = '';
                }
                $key = "{$keyfather}[{$key}]";
            }
            if(is_array($value)) {
                $response = Url::custom_http_build_query($value, $key, $response, false);
            } else {
                if(!is_null($value)) {
                    $response .= "&".urlencode($key)."=".urlencode($value);
                }
            }
        }

        return $ltrim ? ltrim($response, '&') : $response;
    }
}
