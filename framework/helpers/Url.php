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
     * Custom function to emulate http_build_query. The goal is to avoid getting numeric values bettween '[]'.
     * For example:
     * - http_build_query(['language' => ['en', 'ca']])           -> 'language%5B0%5D=en&language%5B1%5D=en'
     * - custom_http_build_query(['language' => ['en', 'ca']])    -> 'language%5B%5D=en&language%5B%5D=ca'
     *
     * @param array  $params Array of values to convert into http query string
     * @param string $inheritKey String used when array has more than one dimesion.
     *                           We pass the our actual key to our child array.
     * @return array
     */
    public static function custom_http_build_query(Array $params, $inheritKey = '') {
        $response = [];
        foreach($params as $key => $value) {
            if(!empty($inheritKey)) {
                if(is_numeric($key)) {
                    $key = '';
                }
                $key = "{$inheritKey}[{$key}]";
            }
            if(is_array($value)) {
                $response[] = Url::custom_http_build_query($value, $key);
            } else {
                if(!is_null($value)) {
                    $response[] = urlencode($key)."=".urlencode($value);
                }
            }
        }

        return implode('&', $response);
    }
}
